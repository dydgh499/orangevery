<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Http\Traits\Models\AttributeTrait;

class PaymentModule extends Model
{
    use HasFactory, AttributeTrait;
    protected   $table      = 'payment_modules';
    protected   $primaryKey = 'id';
    protected   $guarded    = [];
    protected   $hidden     = [
        'brand_id',
    ];
    
    protected function beginDt(): Attribute
    {
        return $this->dateAttribute();
    }

    protected function shipOutDt(): Attribute
    {
        return $this->dateAttribute();
    }

    protected function filterIssuers(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => json_decode($value, true),
        );
    }    
}
