<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StakingTransaction extends Model
{
    use HasFactory;
    protected $fillable = [
        'staking_id', 
        'staking_reward_id', 
        'unsigned_xdr', 
        'signed_xdr', 
        'transaction_id', 
        'staking_status_id',
    ];
}
