<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MultisigTransaction extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'signatures_json' => 'array',
        'required_weight' => 'integer',
        'current_weight' => 'integer',
        'submitted_at' => 'datetime',
    ];

    public const STATUS_PENDING = 'pending';
    public const STATUS_READY = 'ready';
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_FAILED = 'failed';
    public const STATUS_CANCELLED = 'cancelled';

    public function scopeForAccount($query, string $accountId)
    {
        return $query->where('account_id', $accountId);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeReady($query)
    {
        return $query->where('status', self::STATUS_READY);
    }
}
