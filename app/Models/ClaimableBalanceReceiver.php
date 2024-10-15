<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClaimableBalanceReceiver extends Model
{
    use HasFactory;
    protected $fillable = [
        'claimable_balance_id', 
        'receiver_wallet_address',
        'status'
    ];
}
