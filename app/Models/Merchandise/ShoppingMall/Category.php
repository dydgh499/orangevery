<?php

namespace App\Models\Merchandise\ShoppingMall;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Http\Traits\Models\AttributeTrait;
use App\Http\Traits\Models\EncryptDataTrait;
use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Models\Merchandise\ShoppingMall\Product;

class Category extends Model
{
    use HasFactory, AttributeTrait, EncryptDataTrait;
    protected   $table        = 'categories';
    protected   $primaryKey   = 'id';
    protected   $guarded      = [];
    protected   $hidden       = [];
        
    public function productCountGroup()
    {
        return $this->hasMany(Product::class, 'category_id')
            ->selectRaw('category_id, COUNT(*) as product_count')
            ->groupBy('category_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'category_id')
            ->select(['id', 'category_id', 'product_name', 'product_amount', 'product_img']);
    }
}
