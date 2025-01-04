<?php

namespace App\Models\Merchandise\ShoppingMall;

use App\Models\Merchandise\ShoppingMall\ProductOptionGroup;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Http\Traits\Models\AttributeTrait;
use App\Http\Traits\Models\EncryptDataTrait;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Product extends Model
{
    use HasFactory, AttributeTrait, EncryptDataTrait;
    protected   $table        = 'products';
    protected   $primaryKey   = 'id';
    protected   $guarded      = [];
    protected   $hidden       = [];
    
    public function productOptionGroups()
    {
        return $this->hasMany(ProductOptionGroup::class, 'product_id');
    }
}
