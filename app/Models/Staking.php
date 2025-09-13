<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Staking extends Model
{
    protected $guarded = [];

    // Helpful casts for consistent JSON output
    protected $casts = [
        'amount'       => 'decimal:7',
        'apy'          => 'decimal:2',
        'is_withdrawn' => 'boolean',
        'unlock_at'    => 'datetime',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
    ];

    // Relation
    public function asset()
    {
        return $this->belongsTo(StakingAsset::class, 'staking_asset_id');
    }

    // Scopes you’re already conceptually using in the controller
    public function scopeForPublic(Builder $q, string $public): Builder
    {
        return $q->where('public', $public);
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_withdrawn', false);
    }

    public function scopeMinAmount(Builder $q, float $min): Builder
    {
        return $q->where('amount', '>=', $min);
    }
}

