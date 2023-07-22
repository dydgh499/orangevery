<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Salesforce;
use App\Models\Merchandise;
use App\Models\Classification;
use App\Models\PaymentSection;
use App\Http\Traits\Models\AttributeTrait;

use Illuminate\Database\Eloquent\Casts\Attribute;

class Transaction extends Model
{
    use HasFactory, AttributeTrait;
    protected   $table      = 'transactions';
    protected   $primaryKey = 'id';
    protected   $appends    = [
        'profit', 'trx_amount', 'hold_amount',
        'trx_dttm', 'cxl_dttm',
    ];
    protected   $hidden     = ['sales0', 'sales1', 'sales2', 'sales3', 'sales4', 'sales5'];
    protected   $guarded    = [];
    protected   $feeFormatting = false;

    public function scopeSettleTransaction($query, $date)
    {
        return $query->where(function ($query) use ($date) {   // 승인이면서 D+1 적용
                $query->whereRaw("trx_dt < DATE_SUB('$date', INTERVAL(mcht_settle_type) DAY)")
                    ->where('is_cancel', false);
            })->orWhere(function ($query) use ($date) {     // 취소이면서 D+1 적용
                $query->whereRaw("cxl_dt < DATE_SUB('$date', INTERVAL(mcht_settle_type) DAY)")
                    ->where('is_cancel', true);
            });
    }

    public function scopeSettleFilter($query)
    {
        $query = globalPGFilter($query, request());
        return $query->whereNull('mcht_settle_id')
            ->where('brand_id', request()->user()->brand_id);
    }

    private function getProfit($level)
    {
        $getSalesProfit = function($idx) {
            $pks  = ['mcht_id', 'sales0_id','sales1_id','sales2_id','sales3_id','sales4_id','sales5_id','ps_id'];            
            $fees = ['mcht_fee', 'sales0_fee', 'sales1_fee', 'sales2_fee','sales3_fee', 'sales4_fee', 'sales5_fee', 'ps_fee'];
            $dest_fee = $fees[$idx+1];

            for($i=$idx; count($fees)-1 > -1; $i--)
            {
                if($this[$pks[$i]])    // 하위 영업자 - 상위(나의) 영업자, 하위 영업자가 존재할 시, 존재안하면 더 하위로
                    return $this->amount * ($this[$fees[$i]] - $this[$dest_fee]);
            }
            return 0;
        };
        if($level == 10)
        {   // 가맹점
            $profit = $this->amount - ($this->amount * ($this->mcht_fee + $this->hold_fee));
            $profit -= $this->mcht_settle_fee;
        }
        else if($level > 10 && $level < 31)
        {   // 영업자
            $idx    = globalLevelByIndex($level);
            $profit = $getSalesProfit($idx);
            $property = 'sales'.$idx;
            switch($this[$property]->settle_tax_type)
            {
                case 1:
                    $profit *= 0.967;
                    break;
                case 2:
                    $profit *= 0.9;
                    break;
                case 3:                               
                    $profit *= 0.9;
                    $profit *= 0.967;
                    break;
                default:
                    break;
            }
        }
        else
        {   // 본사 
            $profit = $getSalesProfit(6);
            $dev_profit = $profit * $this->dev_fee;
            if($level == 50)
                $profit = $dev_profit;
            else if($level == 40)
                $profit -= $dev_profit;
        }
        return round($profit);
    }

    public function getProfitAttribute()
    {   //정산액
        if(request()->has('level'))
            $level = request()->level;
        else
        {
            if(request()->user()->level != null)
                $level = request()->user()->level;
            else if(request()->user()->mcht_name != null)
                $level = 10;
        }
        return $this->getProfit($level);
    }

    public function getTrxAmountAttribute()
    {   //거래 수수료        
        if(request()->level == 10)
            return round($this->amount * $this->mcht_fee);
        else
            return $this->amount - $this->profit;        
    }

    public function getTotalTrxAmountAttribute()
    {   //총 거래 수수료(거래수수료 + 유보금 + 입금수수료)        
        return $this->amount - $this->profit;
    }
    
    public function getHoldAmountAttribute()
    {   //유보금
        if(request()->level == 10)
            return round($this->amount * $this->hold_fee);
        else
            return 0;        
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
