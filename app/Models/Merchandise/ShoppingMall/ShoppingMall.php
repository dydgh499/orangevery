<?php

namespace App\Models\Merchandise\ShoppingMall;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Http\Traits\Models\AttributeTrait;
use App\Http\Traits\Models\EncryptDataTrait;
use Illuminate\Database\Eloquent\Casts\Attribute;

class ShoppingMall extends Model
{
    use HasFactory, AttributeTrait, EncryptDataTrait;
    protected   $table        = 'shopping_malls';
    protected   $primaryKey   = 'id';
    protected   $guarded      = [];
    protected   $hidden       = [];
}
