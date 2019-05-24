<?php

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
    'transaction' => [
        'table' => 'transactions',
        'model' => \Moecasts\Laravel\Wallet\Models\Transaction::class,
    ],
    'transfer' => [
        'table' => 'transfers',
        'model' => \Moecasts\Laravel\Wallet\Models\Transfer::class,
    ],
    'wallet' => [
        'table' => 'wallets',
        'model' => \Moecasts\Laravel\Wallet\Models\Wallet::class,
        'default' => [
            'currency' => 'POI'
        ],
    ],
];
