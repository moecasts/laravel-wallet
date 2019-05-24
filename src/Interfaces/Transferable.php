<?php

namespace Moecasts\Laravel\Wallet\Interfaces;

use Moecasts\Laravel\Wallet\Models\Wallet;

interface Transferable {
    public function getReceiptWallet(string $currency): Wallet;
}
