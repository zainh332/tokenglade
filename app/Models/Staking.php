<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Staking extends Model
{
    use HasFactory;
    protected $fillable = [
        'public',
        'amount',
        'tier',
        'transaction_id',
        'is_withdrawn',
        'apy',
        'lock_days',
        'unlock_at'
    ];
}
