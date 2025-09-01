<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Token extends Model
{
    use HasFactory;

    protected $fillable = [
        'stellar_token_id',
        'blockchain_id',
    ];

    public function stellarToken()
    {
        return $this->belongsTo(StellarToken::class);
    }

    public function blockchain()
    {
        return $this->belongsTo(Blockchain::class);
    }
}
