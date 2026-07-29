<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TokenWhaleEvent extends Model
{
    use HasFactory;

    protected $table = 'token_whale_events';

    protected $fillable = [
        'asset_code',
        'asset_issuer',
        'transaction_hash',
        'wallet_address',
        'event_type',
        'token_amount',
        'xlm_value',
        'ledger',
        'created_at',
    ];

    protected $casts = [
        'token_amount' => 'float',
        'xlm_value' => 'float',
        'ledger' => 'integer',
    ];
}
