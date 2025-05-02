<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StellarTransactions extends Model
{
    use HasFactory;
    protected $fillable = [
        'stellar_token_id',
        'claimable_balance_id',
        'reclaimable_balance_id',
        
        'user_wallet_address', 
        'transaction_type_id', 
        'unsigned_xdr',
        'signed_xdr',
        'tx_hash',
        'status',
        'meta'
    ];
}   
