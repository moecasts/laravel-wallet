<?php

namespace Moecasts\Laravel\Wallet\Test;

use Moecasts\Laravel\Wallet\Test\Models\TransferableTaxing;
use Moecasts\Laravel\Wallet\Test\Models\User;
use Moecasts\Laravel\Wallet\Test\TestCase;

class TaxTest extends TestCase
{
    public function testTransferWithTax()
    {
        $user = User::firstOrCreate(['name' => 'Test User']);

        $transferable = TransferableTaxing::firstOrCreate(['name' => 'Transferable Item']);

        $wallet = $user->getWallet('POI');

        $wallet->deposit(11);

        $this->assertEquals($wallet->balance, 11);

        $transfer = $wallet->transfer($transferable, 10);

        $this->assertEquals($transfer->fee, 1);
        $this->assertEquals($wallet->balance, 0);
    }
}
