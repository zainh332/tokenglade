<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LiquidityPoolParticipant extends Model
{
    use HasFactory;

    protected $table = 'liquidity_pool_participants';

    protected $fillable = [
        'wallet_address',
        'pool_shares',
        'tkg_amount',
        'xlm_amount',
        'is_active',
        'wallet_status',
    ];

    protected $casts = [
        'pool_shares' => 'float',
        'tkg_amount' => 'float',
        'xlm_amount' => 'float',
        'is_active' => 'boolean',
    ];
}
