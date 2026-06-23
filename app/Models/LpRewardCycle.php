<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LpRewardCycle extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function distributions()
    {
        return $this->hasMany(LpRewardDistribution::class, 'lp_reward_cycle_id');
    }
}
