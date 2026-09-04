<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WatchlistItem extends Model
{
    use HasFactory, HasUuids;

    protected $guarded = [];

    public function marketToken()
    {
        return $this->hasOne(StellarMarketToken::class, 'asset_issuer', 'asset_issuer')
            ->where('asset_code', $this->asset_code);
    }
}
