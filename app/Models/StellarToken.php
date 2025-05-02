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
        'user_wallet_address', 
        'issuerPublicKey', 
        'issuerSecretkey',
        'current_stellar_transaction_id',
        'memo', 
        'lock_status',
        'status'
    ];
}