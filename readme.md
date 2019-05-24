# Wallet

## Feature

- [x] HasWallet
- [x] Deposit
- [x] Withdraw 
- [x] Transfer
- [x] Pay
- [x] Refund

## Installation

### Required
- PHP 7.0+
- Laravel 5.5+

You can install the package using composer

```php
composer require moecasts/laravel-wallet
```

If you are using Laravel < 5.5, you need to add provider to your config/app.php providers array:

```php
Moecasts\Laravel\Wallet\WalletServiceProvider,
```

Publish the mirgrations file:

```bash
php artisan vendor:publish --tag=wallet-migrations
```

As optional if you want to modify the default configuration, you can publish the configuration file:

```bash
php artisan vendor:publish --tag=wallet-config
```

And create tables:

```bash
php artisan migrate
```

Finally, add feature trait into User model:

```php
use Moecasts\Laravel\Wallet\Traits\HasWallets;

class User extends Model
{
    use HasWallets;
}
```


## Configurations

### Currencies

Here you can set supported currencies and its coefficient.

```php
return [
    'currencies' => [
        'POI',
        'COI',
        'CNY'
    ],
    'package' => [
        'coefficient' => [
            'COI' => 100.,
            'POI' => 1
        ],
    ]
];

```

### Wallet

Here you can add default wallet.

```php
return [
    'wallet' => [
        'table' => 'wallets',
        'model' => \Moecasts\Laravel\Wallet\Models\Wallet::class,
        'default' => [
            'currency' => 'POI'
        ],
    ],
];

```

## Usage

### Get Wallet

This function will return the wallet of the currency if the currency is supported.

```php
$wallet = $user->getWallet($currency)

$wallet->balance

// return the user transfers of all his wallets
$user->transfers
// return the wallet transfers
$wallet->transfers

// return the user transactions of all his wallets
$user->transactions
// return the wallet transactions
$wallet->transactions

```

### Deposit

```php
$wallet->deposit($amount, $meta = [], $confirmed = true)

$wallet->deposit(233)
$wallet->deposit(233, ['description' => 'Deposit Testing'])
```

### Deposit

```php
$wallet->withdraw($amount, $meta = [], $confirmed = true)

$wallet->withdraw(233)
$wallet->withdraw(233, ['description' => 'withdraw Testing'])

// forceWithdraw though balance is not enough
$wallet->forceWithdraw(233)
```

### Transfer
Add the `Transferable ` interface to `User` model.

```php
use Moecasts\Laravel\Wallet\Interfaces\Transferable;
use Moecasts\Laravel\Wallet\Traits\HasWallets;

class User extends Model implements Transferable
{
    use HasWallets;
}
```

Then you can do this:

```php
$user = User::find(1);
$transferable = User::find(2);

$wallet = $user->getWallet($currency);

// $wallet->transfer(Transferable $transferable,float $amount,?array $meta = [], string $action = 'transfer'))
$wallet->transfer($transferable, 233);

// This will return null but not exception when it failed.
$wallet->safeTransfer($transferable, 233);
```
Wallet with the same currency of the `Transferable` will receive the payment.

### Pay

Add the `HasWallets` trait and `Product` interface to `Item` model.


```php
use Moecasts\Laravel\Wallet\Interfaces\Product;

class Item extends Model implements Product
{
	use HasWallets;
	
	public function canBePaid(): bool
    {
        return true;
    }

    public function getProductAmount(string $action = 'paid'): float
    {
        switch ($action) {
            case 'paid':
                return 10;
                break;
            
            default:
                return 23.3;
                break;
        }
    }
    
    public function getProductMeta(string $action = 'PAID'): ?array
    {
        return [
            'title' => 'Paid for #' . $this->id
        ];
    }

    public function getReceiptWallet(string $currency): Wallet
    {
        return $this->getWallet($currency);
    }
}
```

If you want to pay to the author, you can do this.

As optional you can remove `HasWallets` trait.

```php
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Moecasts\Laravel\Wallet\Interfaces\Product;

class Item extends Model implements Product
{
	public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
	
	public function canBePaid(): bool
    {
        return true;
    }

    public function getProductAmount(string $action = 'paid'): float
    {
        switch ($action) {
            case 'paid':
                return 10;
                break;
            
            default:
                return 23.3;
                break;
        }
    }

    public function getProductMeta(string $action = 'paid'): ?array
    {
        return [
            'title' => 'Paid for #' . $this->id
        ];
    }

    public function getReceiptWallet(string $currency): Wallet
    {
        return $this->author->getWallet($currency);
    }
}
```

Then you can do this.

```php
$user = User::first();
$product = Item::first();

$wallet = $user->getWallet($currency);

// $wallet->pay(Product $item, string $action = 'paid', bool $force = false)
$wallet->pay($item)
$wallet->pay($item, 'read')

// This will return null but not exception when it failed.
$wallet->safePay($item, 'paid')

// return bool
$wallet->paid($item, $action = 'paid') 
```

## Refund
Add the `Refundable` interface to `Item` model.

```php
use Moecasts\Laravel\Wallet\Interfaces\Product;
use Moecasts\Laravel\Wallet\Interfaces\Refundable;

class Item extends Model implements Product, Refundable
{
	use HasWallets;
	
	public function canBePaid(): bool
    {
        return true;
    }

    public function getProductAmount(string $action = 'paid'): float
    {
        switch ($action) {
            case 'paid':
                return 10;
                break;
            
            default:
                return 23.3;
                break;
        }
    }
    
    public function getProductMeta(string $action = 'PAID'): ?array
    {
        return [
            'title' => 'Paid for #' . $this->id
        ];
    }

    public function getReceiptWallet(string $currency): Wallet
    {
        return $this->getWallet($currency);
    }
}
```

Then you can do this.

```php
// $wallet->refund(Refundable $item, string $action = 'paid')
$wallet->refund($item)
$wallet->refund($item, 'read')

// This will return null but not exception when it failed.
$wallet->safeRefund($item, 'read')
```

### Let's enjoy coding!