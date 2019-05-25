<?php

namespace Moecasts\Laravel\Wallet;

use Moecasts\Laravel\Wallet\Interfaces\Taxing;
use Moecasts\Laravel\Wallet\Interfaces\Transferable;
use Moecasts\Laravel\Wallet\Models\Wallet;

class Tax
{

    /**
     * Consider the fee that the system will receive.
     *
     * @param Wallet $wallet
     * @param float $amount
     * @return float
     */
    public static function fee(Transferable $transferable, Wallet $wallet,float $amount): float
    {
        if ($transferable instanceof Taxing) {
            return (float) ($amount * $wallet->coefficient($wallet->currency) * $transferable->getFeePercent() / 100);
        }

        return 0;
    }

}
