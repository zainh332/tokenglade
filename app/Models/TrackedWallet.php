<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrackedWallet extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'first_viewed_at' => 'datetime',
        'last_viewed_at' => 'datetime',
        'last_refreshed_at' => 'datetime',
        'is_connected_wallet' => 'boolean',
        'is_watchlisted' => 'boolean',
        'is_official_wallet' => 'boolean',
    ];

    public function holdings()
    {
        return $this->hasMany(WalletHolding::class, 'wallet_address', 'wallet_address');
    }

    public function events()
    {
        return $this->hasMany(WalletEvent::class, 'wallet_address', 'wallet_address');
    }

    public function snapshots()
    {
        return $this->hasMany(WalletPortfolioSnapshot::class, 'wallet_address', 'wallet_address');
    }

    public function indexingState()
    {
        return $this->hasOne(WalletIndexingState::class, 'wallet_address', 'wallet_address');
    }

    public function metric()
    {
        return $this->hasOne(WalletMetric::class, 'wallet_address', 'wallet_address');
    }
}
