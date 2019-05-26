<?php

namespace Moecasts\Laravel\Wallet\Test\Models;

use Illuminate\Database\Eloquent\Model;
use Moecasts\Laravel\Wallet\Interfaces\Exchangeable as ExchangeableInterface;
use Moecasts\Laravel\Wallet\Interfaces\Taxing;
use Moecasts\Laravel\Wallet\Traits\HasWallets;

class Exchangeable extends Model implements ExchangeableInterface, Taxing
{
    use HasWallets;

    protected $table = 'users';

    protected $fillable = [
        'name',
    ];

    public function getFeePercent(): float
    {
        return 10;
    }
}
