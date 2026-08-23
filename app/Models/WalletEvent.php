<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletEvent extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'amount' => 'float',
        'counter_amount' => 'float',
        'value_xlm' => 'float',
        'value_usd' => 'float',
        'occurred_at' => 'datetime',
        'metadata_json' => 'array',
    ];

    public function trackedWallet()
    {
        return $this->belongsTo(TrackedWallet::class, 'wallet_address', 'wallet_address');
    }
}
