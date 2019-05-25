<?php

namespace Moecasts\Laravel\Wallet\Test;

use Moecasts\Laravel\Wallet\Test\TestCase;
use Moecasts\Laravel\Wallet\WalletProxy;
use function app;

class ProxyTest extends TestCase
{
    /**
     * @return void
     */
    public function testSimple(): void
    {
        $proxy = app(WalletProxy::class);

        for ($i = 0; $i < 10; $i++) {
            $this->assertEquals($proxy->has($i), false);

            $proxy->set($i, $i);
            $this->assertEquals($proxy->has($i), true);
            $this->assertEquals($proxy->get($i), $i);

            $proxy->fresh();
            $this->assertEquals($proxy->get($i), 0);
        }
    }
}
