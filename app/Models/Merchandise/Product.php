<?php

namespace App\Models\Merchandise;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Http\Traits\Models\AttributeTrait;

class Product extends Model
{
    use HasFactory, AttributeTrait;
    protected   $table      = 'products';
    protected   $primaryKey = 'id';
    protected   $guarded    = [];
    protected   $hidden     = [
        'brand_id',
    ];
}
