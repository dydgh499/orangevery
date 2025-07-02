<?php

namespace App\Models\Merchandise;

use App\Http\Traits\Models\AttributeTrait;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentModule extends Model
{
    use HasFactory, AttributeTrait;
    protected   $table      = 'payment_modules';
    protected   $primaryKey = 'id';
    protected   $guarded    = [];
    protected   $hidden     = [];
    

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
