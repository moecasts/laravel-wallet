<?php

namespace Moecasts\Laravel\Wallet\Traits;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Moecasts\Laravel\Wallet\Exceptions\CurrencyInvalid;
use Moecasts\Laravel\Wallet\Interfaces\Product;
use Moecasts\Laravel\Wallet\Interfaces\Refundable;
use Moecasts\Laravel\Wallet\Models\Transaction;
use Moecasts\Laravel\Wallet\Models\Transfer;
use Moecasts\Laravel\Wallet\Models\Wallet;

trait HasWallets {
    public function wallets(): MorphMany
    {
        return $this->morphMany(Wallet::class, 'holder');
    }

    public function getWallet(string $currency): ?Wallet
    {
        if (! in_array($currency, config('wallet.currencies'))) {
            throw new CurrencyInvalid(trans('wallet::errors.currency_unsupported'));
        }

        $wallet = $this->wallets()->where('currency', $currency)->first();

        if (! $wallet) {
            $wallet = $this->wallets()->create([
                'currency' => $currency
            ]);
        }

        return $wallet;
    }

    public function transactions(): MorphMany
    {
        return $this->morphMany(Transaction::class, 'holder');
    }

    public function transfers(): MorphMany
    {
        return $this->morphMany(Transfer::class, 'from');
    }

    public function paid(Product $product, string $action = Transfer::ACTION_PAID)
    {
        $action = [$action];

        $query = $this->transfers();

        return $query
            ->where('refund', false)
            ->where('to_type', $product->getMorphClass())
            ->where('to_id', $product->getKey())
            ->whereIn('action', $action)
            ->orderBy('id', 'desc')
            ->first();
    }

    public function refund(Refundable $product, string $action = Transfer::ACTION_PAID)
    {
        $transfer = $this->paid($product, $action);

        if (! $transfer) {
            throw (new ModelNotFoundException())
                ->setModel(config('wallet.transfer.model'));
        }

        return \DB::transaction(function () use ($transfer) {
            $transfer->withdraw->update([
                'confirmed' => false,
            ]);

            $transfer->deposit->update([
                'confirmed' => false,
            ]);

            $transfer->update([
                'refund' => true,
            ]);

            return $transfer->fromWallet->refreshBalance() &&
                $transfer->toWallet->refreshBalance();
        });
    }

    public function safeRefund(Product $product, string $action = Transfer::ACTION_PAID)
    {
        try {
            return $this->refund($product, $action);
        } catch (\Throwable $throwable) {
            return false;
        }
    }
}
