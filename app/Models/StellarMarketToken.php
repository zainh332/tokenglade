<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StellarMarketToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_code',
        'asset_issuer',
        'name',
        'image',
        'website',
        'is_verified',
        'current_holders',
        'current_price_usd',
        'current_price_xlm',
        'view_count',
        'search_count',
        'last_viewed_at',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'last_viewed_at' => 'datetime',
    ];

    public function votes()
    {
        return $this->hasMany(
            StellarTokenVote::class,
            'stellar_market_token_id'
        );
    }

    public function trustedVotes()
    {
        return $this->votes()
            ->where('vote_type', 'trusted');
    }

    public function suspiciousVotes()
    {
        return $this->votes()
            ->where('vote_type', 'suspicious');
    }

    public function scamVotes()
    {
        return $this->votes()
            ->where('vote_type', 'scam');
    }
}
