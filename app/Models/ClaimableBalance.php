<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClaimableBalance extends Model
{
    use HasFactory;
    protected $fillable = [
        'distributor_wallet_key', 
        'asset_code', 
        'amount', 
        'memo', 
        'status'
    ];
}
