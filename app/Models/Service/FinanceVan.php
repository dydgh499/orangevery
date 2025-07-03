<?php

namespace App\Models\Service;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Http\Traits\Models\AttributeTrait;
use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Http\Controllers\Ablilty\BrandInfo;

class FinanceVan extends Model
{
    use HasFactory, AttributeTrait;
    protected   $table      = 'finance_vans';
    protected   $primaryKey = 'id';
    protected   $guarded    = [];

    public function scopeDelivery($query)
    {
        if(BrandInfo::isDeliveryBrand())
            return $query->where('id', env('ROUTEUP_FIN_ID', null));
        else
            return $query->where('brand_id', request()->user()->brand_id);
    }
}
