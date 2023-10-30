<?php

namespace App\Models;

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
