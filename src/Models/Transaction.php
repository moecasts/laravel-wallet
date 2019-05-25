<?php

namespace Moecasts\Laravel\Wallet\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Moecasts\Laravel\Wallet\Models\Wallet;

class Transaction extends Model
{
    public const TYPE_DEPOSIT = 'deposit';
    public const TYPE_WITHDRAW = 'withdraw';

    /**
     * @var array
     */
    protected $fillable = [
        'holder_type',
        'holder_id',
        'wallet_id',
        'uuid',
        'type',
        'amount',
        'confirmed',
        'meta',
    ];

    protected $casts = [
        'amount' => 'int',
        'confirmed' => 'bool',
        'meta' => 'json'
    ];

    public function holder(): MorphTo
    {
        return $this->morphTo();
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function scopeDeposit($query)
    {
        return $query->where('type', 'deposit');
    }

    public function scopeWithdraw($query)
    {
        return $query->where('type', 'withdraw');
    }
}
