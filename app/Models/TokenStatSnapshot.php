<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TokenStatSnapshot extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_code',
        'asset_issuer',
        'holders',
        'trustlines',
        'pools_count',
        'liquidity_usd',
        'market_cap_usd',
        'price_usd',
        'circulating_supply',
        'created_at',
    ];

    protected $casts = [
        'holders' => 'integer',
        'trustlines' => 'integer',
        'pools_count' => 'integer',
        'liquidity_usd' => 'float',
        'market_cap_usd' => 'float',
        'price_usd' => 'float',
        'circulating_supply' => 'float',
    ];
}
