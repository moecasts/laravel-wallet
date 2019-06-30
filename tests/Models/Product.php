<?php

namespace Moecasts\Laravel\Wallet\Test\Models;

use Illuminate\Database\Eloquent\Model;
use Moecasts\Laravel\Wallet\Interfaces\Product as ProductInterface;
use Moecasts\Laravel\Wallet\Interfaces\Refundable;
use Moecasts\Laravel\Wallet\Interfaces\Taxing;
use Moecasts\Laravel\Wallet\Models\Wallet;
use Moecasts\Laravel\Wallet\Traits\HasWallets;

class Product extends Model implements ProductInterface, Refundable
{
    use HasWallets;

    protected $table = 'items';

    protected $fillable = [
        'name',
        'price',
        'quantity'
    ];

    public function canBePaid(string $action = 'paid'): bool
    {
        return (int) $this->quantity > 0;
    }

    public function getProductAmount(string $action = 'paid'): float
    {
        return $this->price;
    }

    public function getProductMeta(string $action = 'paid'): array
    {
        return [
            'action' => 'paid',
            'description' => 'Payment for Item #' . $this->id
        ];
    }

    public function getReceiptWallet(string $currency): Wallet
    {
        return $this->getWallet($currency);
    }
}
