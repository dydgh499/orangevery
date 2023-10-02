<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Http\Traits\Models\AttributeTrait;

class PaymentGateway extends Model
{
    use HasFactory, AttributeTrait;
    protected   $table      = 'payment_gateways';
    protected   $primaryKey = 'id';
    protected   $guarded    = [];
    protected   $hidden     = [
        'brand_id',
    ];
}
