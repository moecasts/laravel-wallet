# Wallet

- [中文](readme_ZH.md)
- [English](readme.md)

## 自动化测试

[![Build Status](https://www.travis-ci.org/MoeCasts/laravel-wallet.svg?branch=master)](https://www.travis-ci.org/MoeCasts/laravel-wallet)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/MoeCasts/laravel-wallet/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/MoeCasts/laravel-wallet/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/MoeCasts/laravel-wallet/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/MoeCasts/laravel-wallet/?branch=master)
[![Build Status](https://scrutinizer-ci.com/g/MoeCasts/laravel-wallet/badges/build.png?b=master)](https://scrutinizer-ci.com/g/MoeCasts/laravel-wallet/build-status/master)
[![Code Intelligence Status](https://scrutinizer-ci.com/g/MoeCasts/laravel-wallet/badges/code-intelligence.svg?b=master)](https://scrutinizer-ci.com/code-intelligence)

## 功能

- [x] HasWallet
- [x] 充值
- [x] 取款
- [x] 兑换
- [x] 转账
- [x] 支付
- [x] 退款

## 安装

### 需求

- PHP 7.0+
- Laravel 5.5+

通过 `composer` 安装：

```php
composer require moecasts/laravel-wallet
```

如果你是用 `Laravel` 的版本 < 5.5，则需要手动将 `provide` 添加到 `config/app.php providers` 数组中

```php
Moecasts\Laravel\Wallet\WalletServiceProvider,
```

发布迁移文件：

```bash
php artisan vendor:publish --tag=wallet-migrations
```

如果你想修改默认配置，可以运行下列命令发布配置文件后修改：

```bash
php artisan vendor:publish --tag=wallet-config
```

数据表迁移：

```bash
php artisan migrate
```

最后，添加 `Trait` 到 `User Model` 中：

```php
use Moecasts\Laravel\Wallet\Traits\HasWallets;

class User extends Model
{
    use HasWallets;
}
```

## 配置

### 货币类型

你可以设置允许使用的货币以及它们的系数和汇率。

```php
return [
    'currencies' => [
        'POI',
        'COI',
        'CNY'
    ],
    'coefficient' => [
        'COI' => 100.,
        'POI' => 1
    ],
    'exchange' => [
        'COI' => [
            'POI' => 100,
            'CNY' => 1
        ],
        'CNY' => [
            'COI' => 1
        ]
    ],
];
```

### 钱包

你可以按照下列的方式设置默认钱包。

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

## 用法

### 获取钱包

如果货币在支持列表中，则会返回该货币类型的钱包。

```php
$wallet = $user->getWallet($currency)

$wallet->balance

// 返回用户转账记录
$user->transfers
// 返回钱包转账记录
$wallet->transfers

// 返回用户的收支记录
$user->transactions
// 返回钱包的收支记录
$wallet->transactions

```

### 充值

```php
$wallet->deposit($amount, $meta = [], $confirmed = true)

$wallet->deposit(233)
$wallet->deposit(233, ['description' => 'Deposit Testing'])
```

### 取款

```php
$wallet->withdraw($amount, $meta = [], $confirmed = true)

$wallet->withdraw(233)
$wallet->withdraw(233, ['description' => 'withdraw Testing'])

// 强制取款（即使余额不足也会生效）
$wallet->forceWithdraw(233)
```

### 兑换

添加 `exchange` 配置到 `config/wallet.php`.

```php
return [
    'exchange' => [
        // To be exchanged cuurency
        'COI' => [
            // target currency => (one to be exchanged cuurency = ? target currency)
            'POI' => 100,
            'CNY' => 1
        ],
    ]
];
```

添加 `Exchangeable` Interface 到 `User` Model.

```php
use Illuminate\Database\Eloquent\Model;
use Moecasts\Laravel\Wallet\Interfaces\Exchangeable;
use Moecasts\Laravel\Wallet\Traits\HasWallets;

class User extends Model implements Exchangeable
{
    use HasWallets;
}
```

之后可以这么用：

```php
$wallet = $userWallet->getWallet('COI')

// $wallet->exchange(string $currency, float $mouant)
$wallet->exchange('POI', 10)
// This will return null but not exception when it failed.
$wallet->safeExchange('POI', 10)
// This will exchange though balance is not enough
$wallet->forceExchange('POI', 10)
```

### 转账

添加 `Transferable` Interface 到 `User` Model.

```php
use Moecasts\Laravel\Wallet\Interfaces\Transferable;
use Moecasts\Laravel\Wallet\Traits\HasWallets;

class User extends Model implements Transferable
{
    use HasWallets;
}
```

之后可以这么用：

```php
$user = User::find(1);
$transferable = User::find(2);

$wallet = $user->getWallet($currency);

// $wallet->transfer(Transferable $transferable,float $amount,?array $meta = [], string $action = 'transfer'))
$wallet->transfer($transferable, 233);

// 当有错误发生时返回 null
$wallet->safeTransfer($transferable, 233);
```

`Transferable` 所拥有的同类型货币的钱包将收到货款。

### 支付

添加 `HasWallets` Trait 和 `Product` 到 `Item` Model.

```php
use Moecasts\Laravel\Wallet\Interfaces\Product;

class Item extends Model implements Product
{
    use HasWallets;

    public function canBePaid(string $action = 'paid'): bool
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

如果你想支付给作者，你可以这样做：

如果你不想让商品拥有钱包则可以移除 `HasWallets` Trait。

```php
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Moecasts\Laravel\Wallet\Interfaces\Product;

class Item extends Model implements Product
{
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function canBePaid(string $action = 'paid'): bool
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

之后可以这么用：

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

## 退款

添加 `Refundable` Interface 到 `Item` Model.

```php
use Moecasts\Laravel\Wallet\Interfaces\Product;
use Moecasts\Laravel\Wallet\Interfaces\Refundable;

class Item extends Model implements Product, Refundable
{
    use HasWallets;

    public function canBePaid(string $action = 'paid'): bool
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

之后可以这么用：

```php
// $wallet->refund(Refundable $item, string $action = 'paid')
$wallet->refund($item)
$wallet->refund($item, 'read')

// This will return null but not exception when it failed.
$wallet->safeRefund($item, 'read')
```

### Let's enjoy coding!
