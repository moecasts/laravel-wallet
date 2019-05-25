<?php

namespace Moecasts\Laravel\Wallet\Test\Models;

use Illuminate\Database\Eloquent\Model;
use Moecasts\Laravel\Wallet\Interfaces\Transferable as TransferableInterface;
use Moecasts\Laravel\Wallet\Models\Wallet;
use Moecasts\Laravel\Wallet\Traits\HasWallets;

class Transferable extends Model implements TransferableInterface
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
}
