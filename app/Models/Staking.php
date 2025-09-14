<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Staking extends Model
{
    protected $guarded = [];

    protected $casts = [
        'amount'       => 'decimal:7',
        'apy'          => 'decimal:2',
        'is_withdrawn' => 'boolean',
        'unlock_at'    => 'datetime',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
    ];

    public function asset()
    {
        return $this->belongsTo(StakingAsset::class, 'staking_asset_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeForPublicKey(Builder $q, string $publicKey): Builder
    {
        return $q->whereHas('user', fn ($uq) => $uq->where('public_key', $publicKey));
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_withdrawn', false);
    }

    public function scopeMinAmount(Builder $q, float $min): Builder
    {
        return $q->where('amount', '>=', $min);
    }

    public function rewards()
    {
        return $this->hasMany(StakingReward::class, 'staking_id');
    }
}

