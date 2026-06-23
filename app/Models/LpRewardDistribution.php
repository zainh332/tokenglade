<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LpRewardDistribution extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function cycle()
    {
        return $this->belongsTo(LpRewardCycle::class, 'lp_reward_cycle_id');
    }
}
