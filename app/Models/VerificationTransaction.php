<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VerificationTransaction extends Model
{
    protected $fillable = [
        'verified_project_id',
        'wallet_address',
        'unsigned_xdr',
        'signed_xdr',
        'transaction_hash',
        'amount',
        'status',
        'error_message',
        'verification_payment_asset_id',
        'refunded_amount',
        'refund_transaction_hash',
        'refund_status',
    ];

    const STATUS_PENDING   = 0;
    const STATUS_SUBMITTED = 1;
    const STATUS_SUCCESS   = 2;
    const STATUS_FAILED    = 3;

    public function verifiedProject()
    {
        return $this->belongsTo(VerifiedProject::class);
    }
}
