<?php

namespace Moecasts\Laravel\Wallet\Test;

use Moecasts\Laravel\Wallet\Test\Models\Product;
use Moecasts\Laravel\Wallet\Test\Models\User;
use Moecasts\Laravel\Wallet\Test\TestCase;

class PayTest extends TestCase
{
    /**
      * @expectedException     Moecasts\Laravel\Wallet\Exceptions\InsufficientFunds
      */
    public function testPay()
    {
        $user = User::firstOrCreate(['name' => 'Test User']);

        $product = Product::firstOrCreate([
            'name' => 'Product Item',
            'price' => 233,
            'quantity' => 10
        ]);

        $wallet = $user->getWallet('POI');

        $wallet->deposit(233);
        $this->assertEquals($wallet->balance, 233);

        $payment = $wallet->pay($product);
        $this->assertEquals($wallet->balance, 0);

        $transfer = $wallet->transfers()->where('action', 'paid')->first();
        $this->assertEquals($transfer->getKey(), $payment->getKey());

        $wallet->pay($product);
    }

    public function testforcePay()
    {
        $user = User::firstOrCreate(['name' => 'Test User']);

        $product = Product::firstOrCreate([
            'name' => 'Product Item',
            'price' => 233,
            'quantity' => 10
        ]);

        $wallet = $user->getWallet('POI');

        $this->assertEquals($wallet->balance, 0);

        $payment = $wallet->forcePay($product);
        $this->assertEquals($wallet->balance, -233);

        $transfer = $wallet->transfers()->where('action', 'paid')->first();
        $this->assertEquals($transfer->getKey(), $payment->getKey());
    }

    /**
      * @expectedException     Moecasts\Laravel\Wallet\Exceptions\ProductEnded
      */
    public function testProductEnded()
    {
        $user = User::firstOrCreate(['name' => 'Test User']);

        $product = Product::firstOrCreate([
            'name' => 'Product Item',
            'price' => 233,
            'quantity' => 0
        ]);

        $wallet = $user->getWallet('POI');

        $wallet->deposit(233);
        $this->assertEquals($wallet->balance, 233);

        $payment = $wallet->pay($product);
    }

    public function testSafePay()
    {
        $user = User::firstOrCreate(['name' => 'Test User']);

        $product = Product::firstOrCreate([
            'name' => 'Product Item',
            'price' => 233,
            'quantity' => 10
        ]);

        $wallet = $user->getWallet('POI');

        $this->assertEquals($wallet->balance, 0);

        $payment = $wallet->safePay($product);

        $this->assertEquals($payment, null);
    }

    public function testPaid()
    {
        $user = User::firstOrCreate(['name' => 'Test User']);

        $product = Product::firstOrCreate([
            'name' => 'Product Item',
            'price' => 233,
            'quantity' => 10
        ]);

        $wallet = $user->getWallet('POI');

        $wallet->deposit(233);
        $this->assertEquals($wallet->balance, 233);

        $payment = $wallet->pay($product, 'trial');
        $this->assertEquals($wallet->balance, 0);

        $paid = $wallet->paid($product, 'trial');
        $this->assertEquals((bool) $paid, true);

        $this->assertEquals($wallet->paid($product), false);
    }
}
