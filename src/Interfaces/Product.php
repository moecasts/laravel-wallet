<?php

namespace Moecasts\Laravel\Wallet\Interfaces;

use Moecasts\Laravel\Wallet\Interfaces\Transferable;
use Moecasts\Laravel\Wallet\Models\Transfer;
use Moecasts\Laravel\Wallet\Models\Wallet;

interface Product extends Transferable
{
    public function canBePaid(string $action = Transfer::ACTION_PAID): bool;

    public function getProductAmount(string $action = Transfer::ACTION_PAID): float;

    public function getProductMeta(string $action = Transfer::ACTION_PAID): ?array;
}
