<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TokenLargeEvent extends Model
{
    use HasFactory;

    protected $table = 'token_large_events';

    public $timestamps = false;

    protected $fillable = [
        'asset_code',
        'asset_issuer',
        'transaction_hash',
        'wallet_address',
        'event_type',
        'token_amount',
        'xlm_value',
        'usd_value',
        'ledger',
        'created_at',
    ];
}
