<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StellarToken extends Model
{
    use HasFactory;
    
    protected $guarded = [];

    public function token()
    {
        return $this->hasOne(Token::class);
    }
}