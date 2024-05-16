<?php

namespace App\Models\Merchandise;

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
}
