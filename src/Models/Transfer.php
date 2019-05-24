<?php

namespace Moecasts\Laravel\Wallet\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Moecasts\Laravel\Wallet\Models\Transaction;

class Transfer extends Model
{
    public const ACTION_TRANSFER = 'transfer';
    public const ACTION_READ = 'read';
    public const ACTION_DOWNLOAD = 'download';
    public const ACTION_SUBSCRIBE = 'subscribe';
    public const ACTION_TRIAL = 'trial';
    public const ACTION_PAID = 'paid';
    public const ACTION_REFUND = 'refund';
    public const ACTION_GIFT = 'gift';

    protected $fillable = [
        'deposit_id',
        'withdraw_id',
        'from_type',
        'from_id',
        'from_wallet_id',
        'to_type',
        'to_id',
        'to_wallet_id',
        'action',
        'uuid',
        'fee',
        'refund'
    ];

    protected $casts = [
        'refund' => 'boolean'
    ];

    public function from(): MorphTo
    {
        return $this->morphTo();
    }

    public function fromWallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function to(): MorphTo
    {
        return $this->morphTo();
    }

    public function toWallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function deposit(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'deposit_id');
    }

    public function withdraw(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'withdraw_id');
    }
}
