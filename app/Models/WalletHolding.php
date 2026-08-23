<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletHolding extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'balance' => 'float',
        'price_xlm' => 'float',
        'price_usd' => 'float',
        'value_xlm' => 'float',
        'value_usd' => 'float',
        'allocation_percentage' => 'float',
        'limit' => 'float',
        'is_authorized' => 'boolean',
        'is_authorized_to_maintain_liabilities' => 'boolean',
        'is_clawback_enabled' => 'boolean',
    ];

    public function trackedWallet()
    {
        return $this->belongsTo(TrackedWallet::class, 'wallet_address', 'wallet_address');
    }
}
