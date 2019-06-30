<?php

namespace Moecasts\Laravel\Wallet\Traits;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Moecasts\Laravel\Wallet\Exceptions\ProductEnded;
use Moecasts\Laravel\Wallet\Interfaces\Product;
use Moecasts\Laravel\Wallet\Interfaces\Refundable;
use Moecasts\Laravel\Wallet\Models\Transfer;

trait CanPay {
    public function pay(Product $product, string $action = Transfer::ACTION_PAID, bool $force = false): Transfer
    {
        if (! $product->canBePaid($action)) {
            throw new ProductEnded(trans('wallet::errors.product_stock'));
        }

        if ($force) {
            return $this->forceTransfer(
                $product,
                $product->getProductAmount($action),
                $product->getProductMeta($action),
                $action
            );
        }

        return $this->transfer(
            $product,
            $product->getProductAmount($action),
            $product->getProductMeta($action),
            $action
        );
    }

    public function forcePay(Product $product, string $action = Transfer::ACTION_PAID): Transfer
    {
        return $this->pay($product, $action, true);
    }

    public function safePay(Product $product, string $action = Transfer::ACTION_PAID, bool $force = false): ?Transfer
    {
        try {
            return $this->pay($product, $action, $force);
        } catch (\Throwable $throwable) {
            return null;
        }
    }

    public function paid(Product $product, string $action = Transfer::ACTION_PAID): ?Transfer
    {
        $action = [$action];

        $query = $this->holderTransfers();

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
