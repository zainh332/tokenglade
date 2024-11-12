<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClaimClaimableBalance extends Model
{
    use HasFactory;
    protected $fillable = [
        'distributor_wallet_key', 
        'issuer_address', 
        'asset_code',
        'status'
    ];
}
