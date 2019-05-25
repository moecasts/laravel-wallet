<?php

namespace Moecasts\Laravel\Wallet\Test;

use Moecasts\Laravel\Wallet\Test\Models\Transferable;
use Moecasts\Laravel\Wallet\Test\Models\User;
use Moecasts\Laravel\Wallet\Test\TestCase;

class TransferTest extends TestCase
{
    public function testTransfer()
    {
        $user = User::firstOrCreate(['name' => 'Test User']);

        $transferable = Transferable::firstOrCreate(['name' => 'Transferable Item']);

        $wallet = $user->getWallet('POI');

        $wallet->deposit(233);

        $this->assertEquals($wallet->balance, 233);

        $wallet->transfer($transferable, 33);

        $this->assertEquals($wallet->balance, 200);
        $this->assertEquals($transferable->getWallet($wallet->currency)->balance, 33);
    }

    public function testTransfers()
    {
        $user = User::firstOrCreate(['name' => 'Test User']);

        $transferable = Transferable::firstOrCreate(['name' => 'Transferable Item']);

        // Test first transfer

        $firstWallet = $user->getWallet('POI');

        $firstWallet->deposit(233);

        $this->assertEquals($firstWallet->balance, 233);

        $firstTransfer = $firstWallet->transfer($transferable, 33);

        $this->assertEquals($firstWallet->balance, 200);
        $this->assertEquals($transferable->getWallet($firstWallet->currency)->balance, 33);

        $firstWalletTransferKeys = $firstWallet->transfers->pluck('id')->toArray();

        $this->assertEquals($firstWalletTransferKeys, [$firstTransfer->getKey()]);

        // Test second transfer
        $secondWallet = $user->getWallet('COI');

        $secondWallet->deposit(233);

        $this->assertEquals($secondWallet->balance, 233);

        $secondTransfer = $secondWallet->transfer($transferable, 33);

        $this->assertEquals($secondWallet->balance, 200);
        $this->assertEquals($transferable->getWallet($secondWallet->currency)->balance, 33);

        $secondWalletTransferKeys = $secondWallet->transfers->pluck('id')->toArray();

        $this->assertEquals($secondWalletTransferKeys, [$secondTransfer->getKey()]);

        // Test User transfers
        $transferKeys = $user->transfers->pluck('id')->toArray();

        $this->assertEquals($transferKeys, [$firstTransfer->getKey(), $secondTransfer->getKey()]);
    }
}
