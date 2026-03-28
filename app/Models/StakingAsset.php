<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StakingAsset extends Model
{
    protected $guarded = [];

    protected $casts = [
        'min_stake' => 'decimal:7',
        'is_active' => 'boolean',
    ];

    public function stakings()
    {
        return $this->hasMany(Staking::class, 'staking_asset_id');
    }
}

