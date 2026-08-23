<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletAssetSnapshot extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'balance' => 'float',
        'price_xlm' => 'float',
        'price_usd' => 'float',
        'value_xlm' => 'float',
        'value_usd' => 'float',
        'snapshot_at' => 'datetime',
    ];
}
