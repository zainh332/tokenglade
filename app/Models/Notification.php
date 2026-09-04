<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory, HasUuids;

    protected $guarded = [];

    protected $casts = [
        'read' => 'boolean',
    ];

    protected $appends = [
        'is_read',
        'data',
        'time_ago',
    ];

    public function getIsReadAttribute(): bool
    {
        return (bool) $this->read;
    }

    public function getDataAttribute(): array
    {
        return [
            'asset_code' => $this->asset_code,
            'asset_issuer' => $this->asset_issuer,
            'alert_id' => $this->alert_id,
        ];
    }

    public function getTimeAgoAttribute(): string
    {
        return $this->created_at ? $this->created_at->diffForHumans() : 'just now';
    }

    public function alert()
    {
        return $this->belongsTo(PriceAlert::class, 'alert_id');
    }
}
