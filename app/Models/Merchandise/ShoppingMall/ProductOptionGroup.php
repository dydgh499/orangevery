<?php

namespace App\Models\Merchandise\ShoppingMall;

use App\Models\Merchandise\ShoppingMall\ProductOption;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Http\Traits\Models\AttributeTrait;
use App\Http\Traits\Models\EncryptDataTrait;
use Illuminate\Database\Eloquent\Casts\Attribute;

class ProductOptionGroup extends Model
{
    use HasFactory, AttributeTrait, EncryptDataTrait;
    protected   $table        = 'product_option_groups';
    protected   $primaryKey   = 'id';
    protected   $guarded      = [];
    protected   $hidden       = [];

    public function productOptions()
    {
        return $this->hasMany(ProductOption::class, 'group_id');
    }
}
