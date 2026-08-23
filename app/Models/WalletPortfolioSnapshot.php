<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletPortfolioSnapshot extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'total_value_xlm' => 'float',
        'total_value_usd' => 'float',
        'snapshot_at' => 'datetime',
    ];

    public function assetSnapshots()
    {
        return $this->hasMany(WalletAssetSnapshot::class, 'wallet_address', 'wallet_address')
            ->whereColumn('snapshot_at', 'snapshot_at');
    }
}
