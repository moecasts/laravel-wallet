<?php

namespace Moecasts\Laravel\Wallet;

use Moecasts\Laravel\Wallet\Interfaces\Taxing;
use Moecasts\Laravel\Wallet\Models\Wallet;

class Tax
{

    /**
     * Consider the fee that the system will receive.
     *
     * @param Wallet $wallet
     * @param int $amount
     * @return int
     */
    public static function fee(Wallet $wallet, int $amount): int
    {
        if ($wallet instanceof Taxing) {
            return (int) ($amount * $wallet->coefficient($wallet->currency) * $wallet->getFeePercent() / 100);
        }

        return 0;
    }

}
