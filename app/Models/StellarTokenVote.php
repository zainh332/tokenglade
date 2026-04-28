<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StellarTokenVote extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'stellar_market_token_id',
        'vote_type',
        'vote_weight',
        'last_changed_at',
    ];

    protected $casts = [
        'last_changed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function token()
    {
        return $this->belongsTo(
            StellarMarketToken::class,
            'stellar_market_token_id'
        );
    }
}
