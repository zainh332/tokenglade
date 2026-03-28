<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StakingStatus extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function stakings()
    {
        return $this->hasMany(Staking::class, 'staking_status_id');
    }
}
