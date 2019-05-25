<?php

namespace Moecasts\Laravel\Wallet\Test;

use Moecasts\Laravel\Wallet\WalletProxy;

class TestCase extends \Orchestra\Testbench\TestCase
{

    protected function getPackageProviders($app)
    {
        return ['Moecasts\Laravel\Wallet\WalletServiceProvider'];
    }

    protected function setUp(): void
    {
        WalletProxy::fresh();

        parent::setUp();
        $this->loadMigrationsFrom([
            '--database' => 'testbench',
            '--path' => __DIR__ . '/../database/migrations'
        ]);

        $this->loadMigrationsFrom([
            '--database' => 'testbench',
            '--path' => __DIR__ . '/database/migrations'
        ]);
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }
}
