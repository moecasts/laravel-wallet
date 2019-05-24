<?php

namespace Moecasts\Laravel\Wallet;

use Illuminate\Support\ServiceProvider;

class WalletServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadTranslationsFrom(
            __DIR__ . '/../resources/lang',
            'wallet'
        );

        if (!$this->app->runningInConsole()) {
            return;
        }

        if (\function_exists('config_path')) {
            $this->publishes([
              __DIR__ . '/../config/wallet.php' => config_path('wallet.php')
            ], 'wallet-config');
        }

        $this->publishes([
            __DIR__ . '/../database/migrations' => database_path('migrations'),
        ], 'wallet-migrations');
    }

    public function register()
    {
        $this->mergeConfigFrom(
            \dirname(__DIR__) . '/config/wallet.php',
            'wallet'
        );

        // Bind eloquent models to IoC container
        $this->app->singleton('moecasts.wallet::transaction', config('wallet.transaction.model'));
        $this->app->singleton('moecasts.wallet::transfer', config('wallet.transfer.model'));
        $this->app->singleton('moecasts.wallet::wallet', config('wallet.wallet.model'));
    }
}
