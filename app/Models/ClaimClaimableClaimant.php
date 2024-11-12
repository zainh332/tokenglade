<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClaimClaimableClaimant extends Model
{
    use HasFactory;
    protected $fillable = [
        'claim_claimable_balance_id', 
        'claimants_wallet_address',
        'status'
    ];
}
