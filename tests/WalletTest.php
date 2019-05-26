<?php

namespace Moecasts\Laravel\Wallet\Test;

use Moecasts\Laravel\Wallet\Test\Models\Exchangeable;
use Moecasts\Laravel\Wallet\Test\Models\User;
use Moecasts\Laravel\Wallet\Test\TestCase;
use Moecasts\Laravel\Wallet\WalletProxy;

class WalletTest extends TestCase
{
    public function testWallets()
    {
        $user = User::firstOrCreate(['name' => 'Test User']);

        $poiWallet = $user->getWallet('POI');
        $coiWallet = $user->getWallet('COI');

        $walletKeys = $user->wallets->pluck('id')->toArray();

        $this->assertEquals($walletKeys, [$poiWallet->getKey(), $coiWallet->getKey()]);
    }

    public function testGetWallet()
    {
        $user = User::firstOrCreate(['name' => 'Test User']);

        $createWallet = $user->getWallet('POI');
        $getWallet = $user->getWallet('POI');

        $this->assertEquals(
            $createWallet->getKey(),
            $getWallet->getKey()
        );
    }

    /**
     * @expectedException Moecasts\Laravel\Wallet\Exceptions\CurrencyInvalid
     */
    public function testInvalidWallet()
    {
        $user = User::firstOrCreate(['name' => 'Test User']);

        $getWallet = $user->getWallet('233');
    }

    /**
     * @expectedException Moecasts\Laravel\Wallet\Exceptions\AmountInvalid
     */
    public function testDeposit(): void
    {
        $user = User::firstOrCreate(['name' => 'Test User']);

        $wallet = $user->getWallet('POI');

        $wallet->deposit(233);

        $this->assertEquals($wallet->balance, 233);

        $wallet->deposit(-1);
    }

    /**
      * @expectedException Moecasts\Laravel\Wallet\Exceptions\InsufficientFunds
      */
    public function testWithdraw(): void
    {
        $user = User::firstOrCreate(['name' => 'Test User']);

        $wallet = $user->getWallet('POI');

        $this->assertEquals($wallet->balance, 0);
        $wallet->deposit(100);

        $this->assertEquals($wallet->balance, 100);
        $wallet->withdraw(10);

        $this->assertEquals($wallet->balance, 90);
        $wallet->withdraw(81);

        $this->assertEquals($wallet->balance, 9);
        $wallet->withdraw(9);

        $this->assertEquals($wallet->balance, 0);

        $wallet->withdraw(1);
    }

    public function testForceWithdraw(): void
    {
        $user = User::firstOrCreate(['name' => 'Test User']);

        $wallet = $user->getWallet('POI');

        $this->assertEquals($wallet->balance, 0);

        $wallet->forceWithdraw(233);
        $this->assertEquals($wallet->balance, -233);
    }

    public function testTransactions()
    {
        $user = User::firstOrCreate(['name' => 'Test User']);

        $firstWallet = $user->getWallet('POI');
        $secondWallet = $user->getWallet('COI');

        $firstDeposit = $firstWallet->deposit(233);
        $this->assertEquals($firstWallet->balance, 233);
        // test holder
        $this->assertEquals($firstDeposit->holder->name, $user->name);
        // test wallet
        $this->assertEquals($firstDeposit->wallet->getKey(), $firstWallet->getKey());

        $firstWithdraw = $firstWallet->withdraw(233);
        $this->assertEquals($firstWithdraw->balance, 0);

        $firstWalletTransactionKeys = $firstWallet->transactions->pluck('id')->toArray();
        $this->assertEquals($firstWalletTransactionKeys, [$firstDeposit->getKey(), $firstWithdraw->getKey()]);

        $secondDeposit = $secondWallet->deposit(23.3);
        $this->assertEquals($secondWallet->balance, 23.3);

        $secondWalletTransactionKeys = $secondWallet->transactions->pluck('id')->toArray();
        $this->assertEquals($secondWalletTransactionKeys, [$secondDeposit->getKey()]);

        $depositTransactionKeys = $user->transactions()->deposit()->get()->pluck('id')->toArray();
        $this->assertEquals($depositTransactionKeys, [$firstDeposit->getKey(), $secondDeposit->getKey()]);

        $withdrawTransactionKeys = $user->transactions()->withdraw()->get()->pluck('id')->toArray();
        $this->assertEquals($withdrawTransactionKeys, [$firstWithdraw->getKey()]);
    }

    // Tax fee = 10%
    public function testExchage()
    {
        $exchangeable = Exchangeable::firstOrCreate(['name' => 'Test User']);

        $wallet = $exchangeable->getWallet('COI');

        $wallet->deposit(11);

        $this->assertEquals($wallet->balance, 11);

        $exchange = $wallet->exchange('POI', 10);

        $this->assertEquals($exchange->fromWallet->balance, 0);
        $this->assertEquals($exchange->toWallet->balance, 1000);
    }

    /**
     * @expectedException Moecasts\Laravel\Wallet\Exceptions\ExchangeInvalid
     */
    public function testInvalidExchange()
    {
        $exchangeable = Exchangeable::firstOrCreate(['name' => 'Test User']);

        $wallet = $exchangeable->getWallet('POI');

        $wallet->deposit(10);

        $this->assertEquals($wallet->balance, 10);

        $exchange = $wallet->exchange('CNY', 10);
    }

    /**
     * @expectedException Moecasts\Laravel\Wallet\Exceptions\ExchangeInvalid
     */
    public function testInvalidExchangeable()
    {
        $exchangeable = User::firstOrCreate(['name' => 'Test User']);

        $wallet = $exchangeable->getWallet('CNY');

        $wallet->deposit(11);

        $this->assertEquals($wallet->balance, 11);

        $exchange = $wallet->exchange('POI', 10);
    }

    public function testSafeExchange()
    {
        $exchangeable = Exchangeable::firstOrCreate(['name' => 'Test User']);

        $wallet = $exchangeable->getWallet('POI');

        $wallet->deposit(10);

        $this->assertEquals($wallet->balance, 10);

        $exchange = $wallet->safeExchange('CNY', 10);

        $this->assertEquals($exchange, null);
    }

    public function testForceExchange()
    {
        $exchangeable = Exchangeable::firstOrCreate(['name' => 'Test User']);

        $wallet = $exchangeable->getWallet('COI');

        $this->assertEquals($wallet->balance, 0);

        $exchange = $wallet->forceExchange('POI', 10);

        // Tax fee = 10%
        $this->assertEquals($exchange->fromWallet->balance, -11);
        $this->assertEquals($exchange->toWallet->balance, 1000);
    }

    /**
     * @expectedException Moecasts\Laravel\Wallet\Exceptions\ExchangeInvalid
     */
    public function testInvalidForceExchange()
    {
        $exchangeable = Exchangeable::firstOrCreate(['name' => 'Test User']);

        $wallet = $exchangeable->getWallet('POI');

        $wallet->deposit(10);

        $this->assertEquals($wallet->balance, 10);

        $exchange = $wallet->forceExchange('CNY', 10);
    }

    /**
     * @expectedException Moecasts\Laravel\Wallet\Exceptions\ExchangeInvalid
     */
    public function testInvalidForceExchangeable()
    {
        $exchangeable = User::firstOrCreate(['name' => 'Test User']);

        $wallet = $exchangeable->getWallet('CNY');

        $wallet->deposit(11);

        $this->assertEquals($wallet->balance, 11);

        $exchange = $wallet->forceExchange('POI', 10);
    }

    public function testRefreshBalance()
    {
        $user = User::firstOrCreate(['name' => 'Test User']);

        $wallet = $user->getWallet('POI');

        $wallet->deposit(10);

        $this->assertEquals($wallet->balance, 10);

        $wallet->update([
            'balance' => 0
        ]);

        WalletProxy::set($wallet->getKey(), 0);

        $this->assertEquals($wallet->balance, 0);

        $wallet->refreshBalance();

        $this->assertEquals($wallet->balance, 10);
    }
}
