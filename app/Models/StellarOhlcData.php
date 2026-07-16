<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StellarOhlcData extends Model
{
    use HasFactory;

    protected $table = 'stellar_ohlc_data';

    protected $fillable = [
        'asset_code',
        'asset_issuer',
        'timeframe',
        'timestamp',
        'open',
        'high',
        'low',
        'close',
        'volume',
    ];

    protected $casts = [
        'timestamp' => 'integer',
        'open' => 'float',
        'high' => 'float',
        'low' => 'float',
        'close' => 'float',
        'volume' => 'float',
    ];
}
