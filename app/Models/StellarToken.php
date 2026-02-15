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

    public function transactions()
    {
        return $this->hasMany(StellarTransactions::class);
    }

    public function mintTransaction()
    {
        return $this->hasOne(StellarTransactions::class)
            ->where('transaction_type_id', 4)
            ->latest();
    }
}