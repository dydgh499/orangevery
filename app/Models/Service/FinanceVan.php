<?php

namespace App\Models\Service;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Http\Traits\Models\AttributeTrait;
use App\Http\Controllers\Ablilty\BrandInfo;

class FinanceVan extends Model
{
    use HasFactory, AttributeTrait;
    protected   $table      = 'finance_vans';
    protected   $primaryKey = 'id';
    protected   $guarded    = [];

    public function scopeDelivery($query)
    {
        return $query->where('brand_id', request()->user()->brand_id);
    }
}
