<?php

namespace Moecasts\Laravel\Wallet\Test;

use Moecasts\Laravel\Wallet\Test\Models\Product;
use Moecasts\Laravel\Wallet\Test\Models\User;
use Moecasts\Laravel\Wallet\Test\TestCase;


class RefundTest extends TestCase
{
    /**
      * @expectedException     Illuminate\Database\Eloquent\ModelNotFoundException
      */
    public function testRefund()
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

        $this->assertEquals((bool) $wallet->paid($product), true);

        $refund = $user->refund($product);

        $this->assertEquals($refund, true);

        $this->assertEquals((bool) $wallet->paid($product), false);

        $wallet = $user->getWallet('POI');
        $this->assertEquals($wallet->balance, 233);

        $user->refund($product);
    }

    public function testSafeRefund()
    {
        $user = User::firstOrCreate(['name' => 'Test User']);

        $product = Product::firstOrCreate([
            'name' => 'Product Item',
            'price' => 233,
            'quantity' => 10
        ]);

        $wallet = $user->getWallet('POI');

        $refund = $user->safeRefund($product);

        $this->assertEquals($refund, false);
    }

    /**
      * @expectedException     Illuminate\Database\Eloquent\ModelNotFoundException
      */
    public function testWalletRefund()
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

        $this->assertEquals((bool) $wallet->paid($product), true);

        $refund = $wallet->refund($product);

        $this->assertEquals($refund, true);

        $this->assertEquals((bool) $wallet->paid($product), false);

        $wallet = $user->getWallet('POI');
        $this->assertEquals($wallet->balance, 233);

        $wallet->refund($product);
    }

    public function testWalletSafeRefund()
    {
        $user = User::firstOrCreate(['name' => 'Test User']);

        $product = Product::firstOrCreate([
            'name' => 'Product Item',
            'price' => 233,
            'quantity' => 10
        ]);

        $wallet = $user->getWallet('POI');

        $refund = $wallet->safeRefund($product);

        $this->assertEquals($refund, false);
    }
}
