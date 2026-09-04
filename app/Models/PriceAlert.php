<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PriceAlert extends Model
{
    use HasFactory, HasUuids;

    protected $guarded = [];

    protected $casts = [
        'channels' => 'array',
        'condition_value' => 'float',
        'initial_price_xlm' => 'float',
        'initial_price_usd' => 'float',
        'fired_at' => 'datetime',
    ];

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'alert_id');
    }

    public function marketToken()
    {
        return $this->hasOne(StellarMarketToken::class, 'asset_issuer', 'asset_issuer')
            ->where('asset_code', $this->asset_code);
    }
}
