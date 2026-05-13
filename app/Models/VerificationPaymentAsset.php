<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VerificationPaymentAsset extends Model
{
    use HasFactory;

    protected $fillable = [

        'asset_code',
        'asset_issuer',

        'amount',

        'is_active',
        'position',
    ];

    protected $casts = [

        'amount' => 'decimal:7',
        'usd_value' => 'decimal:7',

        'is_active' => 'boolean',
    ];
}