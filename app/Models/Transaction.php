<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Salesforce;
use App\Models\Merchandise;
use App\Models\Log\RealtimeSendHistory;

use App\Http\Traits\Models\AttributeTrait;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Casts\Attribute;

class Transaction extends Model
{
    use HasFactory, AttributeTrait;
    protected   $table      = 'transactions';
    protected   $primaryKey = 'id';
    protected   $appends    = ['trx_amount', 'hold_amount', 'trx_dttm', 'cxl_dttm', 'total_trx_amount'];
    protected   $hidden     = [
        'sales0', 'sales1', 'sales2', 'sales3', 'sales4', 'sales5',
        'brand_settle_amount',
        'dev_settle_amount',
        'dev_settle_id',
        'mcht_settle_amount',
        'mcht_settle_id',
        'sales0_settle_amount',
        'sales0_settle_id',
        'sales1_settle_amount',
        'sales1_settle_id',
        'sales2_settle_amount',
        'sales2_settle_id',
        'sales3_settle_amount',
        'sales3_settle_id',
        'sales4_settle_amount',
        'sales4_settle_id',
        'sales5_settle_amount',
        'sales5_settle_id',        
    ];
    protected   $guarded    = [];
    protected   $feeFormatting = false;

    public function scopeGlobalFilter($query)
    {
        $query = $query->where('is_delete', false);
        $query = globalPGFilter($query, request());
        $query = globalSalesFilter($query, request());
        $query = globalAuthFilter($query, request());
        return $query;
    }

    public function scopeSettleTransaction($query)
    {
        $date = request()->dt;
        if(request()->input('is_base_trx', 'false') == 'true')
        {
            $trx_dt = 'trx_dt';
            $cxl_dt = 'cxl_dt';
        }
        else
        {
            $trx_dt = "AddBaseWorkingDays(trx_dt, mcht_settle_type+1, pg_settle_type)";
            $cxl_dt = "AddBaseWorkingDays(cxl_dt, mcht_settle_type+1, pg_settle_type)";
        }
        return $query->where(function ($query) use ($date, $trx_dt) {      
                $query->whereRaw("$trx_dt <= '$date'")->where('is_cancel', false);
            })->orWhere(function ($query) use ($date ,$cxl_dt) {
                $query->whereRaw("$cxl_dt <= '$date'")->where('is_cancel', true);
            });
    }

    public function scopeSettleFilter($query, $target)
    {
        return $query->whereNull($target)
            ->where('brand_id', request()->user()->brand_id);
    }

    public function scopeNullSettleFilter($query, $target, $id)
    {
        return $query->where($target, $id)
            ->where('brand_id', request()->user()->brand_id);
    }

    public function getTrxAmountAttribute()
    {   //거래 수수료(거래수수료 + 유보금 + 입금수수료)        
        $trx_amount = $this->amount - $this->profit;
        $trx_amount = request()->level == 10 ? $trx_amount - $this->mcht_settle_fee : $trx_amount;
        return $trx_amount;
    }

    public function getTotalTrxAmountAttribute()
    {
        return $this->amount - $this->profit;
    }

    public function getHoldAmountAttribute()
    {   //유보금
        if(request()->level == 10)
            return round($this->amount * $this->hold_fee);
        else
            return 0;        
    }
    
    public function realtimes()
    {
        return $this->hasMany(RealtimeSendHistory::class, 'trans_id');
    }

    public function mcht()
    {
        return $this->belongsTo(Merchandise::class, 'mcht_id')->select([
            'id', 'mcht_name', 'user_name', 'nick_name',
            'addr', 'resident_num', 'business_num', 
            'use_saleslip_prov', 'use_saleslip_sell',
            'is_show_fee',
        ]);
    }
    // trans
    public function getTrxDttmAttribute()
    {
        return $this->trx_dt." ".$this->trx_tm;
    }
    
    public function getCxlDttmAttribute()
    {
        return $this->cxl_dt." ".$this->cxl_tm;
    }
    
    protected function mchtSettleFee() : Attribute
    {
        return new Attribute(
            get: fn ($value) => request()->level == 10 ? $value : 0,
        );
    }
}
