<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClaimClaimableBalanceId extends Model
{
    use HasFactory;
    protected $fillable = [
        'claim_claimable_balance_id', 
        'balance_id',
        'status'
    ];
}
