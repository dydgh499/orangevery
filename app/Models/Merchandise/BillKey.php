<?php

namespace App\Models\Merchandise;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Http\Traits\Models\AttributeTrait;
use App\Http\Traits\Models\EncryptDataTrait;
use Illuminate\Database\Eloquent\Casts\Attribute;

class BillKey extends Model
{
    use HasFactory, AttributeTrait, EncryptDataTrait;
    protected   $table        = 'bill_keys';
    protected   $primaryKey   = 'id';
    protected   $guarded      = [];
    protected   $hidden       = [
        'ori_bill_key',
        'bill_key',
    ];

    protected function oriBillKey(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->aes256_decode($value),
            set: fn ($value) => $this->aes256_encode($value),
        );
    }

    protected function issuer(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->aes256_decode($value),
            set: fn ($value) => $this->aes256_encode($value),
        );
    }
    
    protected function buyerName(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->aes256_decode($value),
            set: fn ($value) => $this->aes256_encode($value),
        );
    }

    protected function buyerPhone(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->aes256_decode($value),
            set: fn ($value) => $this->aes256_encode($value),
        );
    }
}
