<?php

namespace App\Models\Logs;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Salesforce;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Casts\Attribute;

class SfFeeChangeHistory extends Model
{
    use HasFactory;
    protected   $table      = 'sf_fee_change_histories';
    protected   $primaryKey = 'id';
    protected   $appends    = ['bf_sales_name', 'aft_sales_name'];
    protected   $guarded    = [];
    protected   $hidden     = [
        'bfSales',
        'aftSales',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format("Y-m-d H:i:s");
    }

    public function bfSales()
    {
        return $this->belongsTo(Salesforce::class, 'bf_sales_id')->select(['id', 'nick_name']);
    }

    public function aftSales()
    {
        return $this->belongsTo(Salesforce::class, 'aft_sales_id')->select(['id', 'nick_name']);
    }
    
    public function getBfSalesNameAttribute()
    {
        return $this->bfSales ? $this->bfSales->nick_name : null;
    }

    public function getAftSalesNameAttribute()
    {
        return $this->aftSales ? $this->aftSales->nick_name : null;
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
