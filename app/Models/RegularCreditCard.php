<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Http\Traits\Models\AttributeTrait;

class RegularCreditCard extends Model
{
    use HasFactory, AttributeTrait;
    protected   $table        = 'regular_credit_cards';
    protected   $primaryKey   = 'id';
    protected   $guarded      = [];
    
    protected function cardNum(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->aes256_decode($value),
            set: fn ($value) => $this->aes256_encode($value),
        );
    }
}
