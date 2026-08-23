<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletIndexingState extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'first_indexed_at' => 'datetime',
        'last_indexed_at' => 'datetime',
        'historical_index_complete' => 'boolean',
    ];

    public function trackedWallet()
    {
        return $this->belongsTo(TrackedWallet::class, 'wallet_address', 'wallet_address');
    }
}
