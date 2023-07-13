<?php

namespace App\Models\Log;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use App\Models\Salesforce;
use App\Http\Traits\Models\AttributeTrait;

class SfFeeChangeHistory extends Model
{
    use HasFactory, AttributeTrait;
    protected   $table      = 'sf_fee_change_histories';
    protected   $primaryKey = 'id';
    protected   $appends    = ['bf_sales_name', 'aft_sales_name'];
    protected   $guarded    = [];
    protected   $hidden     = [
        'bfSales',
        'aftSales',
    ];
    
    public function bfSales()
    {
        return $this->belongsTo(Salesforce::class, 'bf_sales_id')->select(['id', 'sales_name']);
    }

    public function aftSales()
    {
        return $this->belongsTo(Salesforce::class, 'aft_sales_id')->select(['id', 'sales_name']);
    }
    
    public function getBfSalesNameAttribute()
    {
        return $this->bfSales ? $this->bfSales->sales_name : null;
    }

    public function getAftSalesNameAttribute()
    {
        return $this->aftSales ? $this->aftSales->sales_name : null;
    }

    protected function BfTrxFee() : Attribute
    {
        return new Attribute(
            get: fn ($value) => number_format($value * 100, 3),
        );
    }

    protected function AftTrxFee() : Attribute
    {
        return new Attribute(
            get: fn ($value) => number_format($value * 100, 3),
        );
    }
}
