<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletMetric extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'portfolio_value_xlm' => 'float',
        'portfolio_value_usd' => 'float',
        'buy_volume_xlm_24h' => 'float',
        'sell_volume_xlm_24h' => 'float',
        'buy_volume_xlm_7d' => 'float',
        'sell_volume_xlm_7d' => 'float',
        'average_trade_size_xlm' => 'float',
        'largest_trade_xlm' => 'float',
        'last_calculated_at' => 'datetime',
    ];

    public function trackedWallet()
    {
        return $this->belongsTo(TrackedWallet::class, 'wallet_address', 'wallet_address');
    }
}
