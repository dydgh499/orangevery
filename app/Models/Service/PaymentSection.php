<?php

namespace App\Models\Service;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Http\Traits\Models\AttributeTrait;

class PaymentSection extends Model
{
    use HasFactory, AttributeTrait;
    protected   $table      = 'payment_sections';
    protected   $primaryKey = 'id';
    protected   $guarded    = [];
    protected   $hidden     = [
        'brand_id',
    ];
    
    protected function TrxFee() : Attribute
    {
        return new Attribute(
            get: fn ($value) => number_format($value * 100, 3),
        );
    }
}
