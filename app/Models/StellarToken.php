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
        'issuer_public_key', 
        'issuer_secret_key',
        'current_stellar_transaction_id',
        'memo', 
        'lock_status',
        'issuer_wallet_status',
        'created_token_transfer_status'
    ];
}