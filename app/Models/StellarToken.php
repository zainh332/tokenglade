<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StellarToken extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'asset_code', 
        'total_supply', 
        'distributor_wallet_key', 
        'issuerPublicKey', 
        'issuerSecretkey',
        'memo', 
        'lock_status',
        'status'
    ];
}