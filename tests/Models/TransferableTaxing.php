<?php

namespace Moecasts\Laravel\Wallet\Test\Models;

use Illuminate\Database\Eloquent\Model;
use Moecasts\Laravel\Wallet\Interfaces\Taxing;
use Moecasts\Laravel\Wallet\Interfaces\Transferable as TransferableInterface;
use Moecasts\Laravel\Wallet\Models\Wallet;
use Moecasts\Laravel\Wallet\Traits\HasWallets;

class TransferableTaxing extends Model implements TransferableInterface, Taxing
{
    use HasWallets;

    protected $table = 'items';

    protected $fillable = [
        'name',
        'price',
        'quantity'
    ];

    public function getReceiptWallet(string $currency): Wallet
    {
        return $this->getWallet($currency);
    }

    public function getFeePercent(): float
    {
        return 10;
    }
}
