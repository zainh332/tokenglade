<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class StakingTier extends Model
{
    protected $guarded = [];

    protected $casts = [
        'tier'        => 'integer',
        'min_amount'  => 'float',
        'max_amount'  => 'float',
        'apy'         => 'float',
        'is_active'   => 'boolean',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
    ];

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true);
    }

    public function scopeOrdered(Builder $q): Builder
    {
        return $q->orderBy('min_amount', 'asc');
    }

    /**
     * Get platform-wide minimum stake threshold from active tiers.
     */
    public static function getMinAmount(): float
    {
        $min = static::active()->min('min_amount');
        return $min !== null ? (float) $min : 1500.0;
    }

    /**
     * Dynamically resolve tier and APY for a given total staked amount.
     * Returns [$tier, $apy, $tierName].
     */
    public static function resolveTierAndApy(float $total): array
    {
        $tiers = static::active()
            ->orderBy('min_amount', 'desc')
            ->get();

        if ($tiers->isEmpty()) {
            // Fallback to defaults if no tiers defined in DB
            if ($total >= 100_000) return [4, 18.00, 'Tier 4'];
            if ($total >= 50_000)  return [3, 16.00, 'Tier 3'];
            if ($total >= 10_000)  return [2, 15.00, 'Tier 2'];
            if ($total >= 1_500)   return [1, 12.00, 'Tier 1'];
            return [0, 0.00, 'None'];
        }

        foreach ($tiers as $tier) {
            if ($total >= (float) $tier->min_amount) {
                return [
                    (int) $tier->tier,
                    (float) $tier->apy,
                    $tier->name ?? ('Tier ' . $tier->tier)
                ];
            }
        }

        return [0, 0.00, 'None'];
    }
}
