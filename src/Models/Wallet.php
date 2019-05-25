<?php

namespace Moecasts\Laravel\Wallet\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\DB;
use Moecasts\Laravel\Wallet\Exceptions\AmountInvalid;
use Moecasts\Laravel\Wallet\Exceptions\InsufficientFunds;
use Moecasts\Laravel\Wallet\Interfaces\Transferable;
use Moecasts\Laravel\Wallet\Models\Transaction;
use Moecasts\Laravel\Wallet\Tax;
use Moecasts\Laravel\Wallet\Traits\CanPay;
use Moecasts\Laravel\Wallet\WalletProxy;
use Ramsey\Uuid\Uuid;

class Wallet extends Model
{
    use CanPay;

    protected $fillable = [
        'holder_type',
        'holder_id',
        'currency',
        'balance'
    ];

    public function holder(): MorphTo
    {
        return $this->morphTo();
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function holderTransfers(): MorphMany
    {
        return $this->holder->transfers();
    }

    public function transfers(): HasMany
    {
        return $this->hasMany(Transfer::class, 'from_wallet_id');
    }

    public function deposit(float $amount, ?array $meta = null, bool $confirmed = true): Transaction
    {
        $this->checkAmount($amount);

        $amount = (int) ($amount * $this->coefficient($this->currency));

        return $this->change(Transaction::TYPE_DEPOSIT, $amount, $meta, $confirmed);
    }

    private function checkAmount(int $amount): void
    {
        if ($amount < 0) {
            throw new AmountInvalid(trans('wallet::errors.price_positive'));
        }
    }

    protected function change(string $type, int $amount, ?array $meta, bool $confirmed): Transaction
    {
        return DB::transaction(function () use ($type, $amount, $meta, $confirmed) {
            if ($confirmed) {
                $this->addBalance($amount);
            }

            return $this->transactions()->create([
                'type' => $type,
                'holder_type' => $this->holder->getMorphClass(),
                'holder_id' => $this->holder->getKey(),
                'wallet_id' => $this->getKey(),
                'uuid' => Uuid::uuid4()->toString(),
                'confirmed' => $confirmed,
                'amount' => $amount,
                'meta' => $meta,
            ]);
        });
    }

    protected function addBalance(float $amount): bool
    {
        $newBalance = $this->attributes['balance'] + $amount;
        $this->balance = $newBalance;
        $finalBalance = $newBalance / $this->coefficient($this->attributes['currency']);

        return
            // update database wallet
            $this->save() &&

            // update static wallet
            WalletProxy::set($this->getKey(), $finalBalance);
    }

    public function getBalanceAttribute(): float
    {
        $this->exists or $this->save();

        if (! WalletProxy::has($this->getKey())) {
            $balance = $this->attributes['balance'] / $this->coefficient($this->attributes['currency']);
            WalletProxy::set($this->getKey(), (float) ($balance ?? 0));
        }

        return WalletProxy::get($this->getKey());
    }

    public function safeTransfer(Transferable $transferable, float $amount, ?array $meta = null, string $action = Transfer::ACTION_TRANSFER): ?Transfer
    {
        try {
            return $this->transfer($transferable, $amount, $meta, $action);
        } catch (\Throwable $throwable) {
            return null;
        }
    }

    public function transfer(Transferable $transferable, float $amount, ?array $meta = null, string $action = Transfer::ACTION_TRANSFER): Transfer
    {
        $wallet = $transferable->getReceiptWallet($this->currency);

        return DB::transaction(function () use ($transferable, $amount, $wallet, $meta, $action) {
            $fee = Tax::fee($transferable, $wallet, $amount);
            $withdraw = $this->withdraw($amount + $fee, $meta);
            $deposit = $wallet->deposit($amount, $meta);
            return $this->assemble($transferable, $wallet, $withdraw, $deposit, $action);
        });
    }

    public function withdraw(float $amount, ?array $meta = null, bool $confirmed = true): Transaction
    {
        if (! $this->canWithdraw($amount)) {
            throw new InsufficientFunds(trans('wallet::errors.insufficient_funds'));
        }

        return $this->forceWithdraw($amount, $meta, $confirmed);
    }

    public function canWithdraw($amount): bool
    {
        return $this->balance >= $amount;
    }

    public function forceWithdraw(float $amount, ?array $meta = null, bool $confirmed = true): Transaction
    {
        $amount = (int) ($amount * $this->coefficient($this->currency));

        $this->checkAmount($amount);

        return $this->change(Transaction::TYPE_WITHDRAW, -$amount, $meta, $confirmed);
    }

    protected function assemble(Transferable $transferable, Wallet $wallet, Transaction $withdraw, Transaction $deposit, string $action = Transfer::ACTION_PAID): Transfer
    {
        return \app('moecasts.wallet::transfer')->create([
            'action' => $action,
            'deposit_id' => $deposit->getKey(),
            'withdraw_id' => $withdraw->getKey(),
            'from_type' => $this->holder->getMorphClass(),
            'from_id' => $this->holder->getKey(),
            'from_wallet_id' => $this->getKey(),
            'to_type' => $transferable->getMorphClass(),
            'to_id' => $transferable->getKey(),
            'to_wallet_id' => $wallet->getKey(),
            'fee' => \abs($withdraw->amount) - \abs($deposit->amount),
            'uuid' => Uuid::uuid4()->toString(),
        ]);
    }

    public function forceTransfer(Transferable $transferable, float $amount, ?array $meta = null, string $action = Transfer::ACTION_TRANSFER): Transfer
    {
        $wallet = $transferable->getReceiptWallet($this->currency);

        return DB::transaction(function () use ($transferable, $amount, $wallet, $meta, $action) {
            $fee = Tax::fee($transferable, $wallet, $amount);
            $withdraw = $this->forceWithdraw($amount + $fee, $meta);
            $deposit = $wallet->deposit($amount, $meta);
            return $this->assemble($transferable, $wallet, $withdraw, $deposit, $action);
        });
    }

    public function refreshBalance(): bool
    {
        $balance = $this->getAvailableBalance();

        $this->attributes['balance'] = $balance;

        WalletProxy::set($this->getKey(), $balance);

        return $this->save();
    }

    public function getAvailableBalance(): int
    {
        return $this->transactions()
            ->where('wallet_id', $this->getKey())
            ->where('confirmed', true)
            ->sum('amount');
    }

    public function coefficient(string $currency = ''): float
    {
        return config('wallet.coefficient.' . $currency , 100.);
    }
}
