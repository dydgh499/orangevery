<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;

use App\Models\Salesforce;
use App\Models\Merchandise;
use App\Models\Classification;
use App\Models\PaymentSection;

use Illuminate\Database\Eloquent\Casts\Attribute;

class Transaction extends Model
{
    use HasFactory;
    protected   $table      = 'transactions';
    protected   $primaryKey = 'id';
    protected   $appends    = [
        'profit', 'trx_amount', 'hold_amount',
        'sales0_name', 'sales1_name', 
        'sales2_name', 'sales3_name', 
        'sales4_name', 'sales5_name', 
        'user_name', 'mcht_name', 'nick_name', 'addr', 'detail_addr', 'resident_num', 'business_num',
        'trx_dttm', 'cxl_dttm',
    ];
    protected   $hidden     = [
        'sales0','sales1',
        'sales2','sales3',
        'sales4','sales5',
        'mcht',
    ];
    protected   $guarded    = [];

    public function getProfit($level)
    {
        $getSalesProfit = function($idx) {
            $pks  = ['mcht_id', 'sales0_id','sales1_id','sales2_id','sales3_id','sales4_id','sales5_id','ps_id'];            
            $fees = ['mcht_fee', 'sales0_fee', 'sales1_fee', 'sales2_fee','sales3_fee', 'sales4_fee', 'sales5_fee', 'ps_fee'];
            $dest_fee = $fees[$idx+1];
            for($i=$idx; count($fees)-1 > -1; $i--)
            {
                if($this[$pks[$i]] != 0)    // 하위 영업자 - 상위(나의) 영업자, 하위 영업자가 존재할 시, 존재안하면 더 하위로
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
        return $this->getProfit(request()->level);
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

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format("Y-m-d H:i:s");
    }
    //sales
    public function sales0()
    {
        return $this->belongsTo(Salesforce::class, 'sales0_id')->select(['id', 'nick_name', 'settle_tax_type']);
    }

    public function sales1()
    {
        return $this->belongsTo(Salesforce::class, 'sales1_id')->select(['id', 'nick_name', 'settle_tax_type']);
    }

    public function sales2()
    {
        return $this->belongsTo(Salesforce::class, 'sales2_id')->select(['id', 'nick_name', 'settle_tax_type']);
    }

    public function sales3()
    {
        return $this->belongsTo(Salesforce::class, 'sales3_id')->select(['id', 'nick_name', 'settle_tax_type']);
    }

    public function sales4()
    {
        return $this->belongsTo(Salesforce::class, 'sales4_id')->select(['id', 'nick_name', 'settle_tax_type']);
    }

    public function sales5()
    {
        return $this->belongsTo(Salesforce::class, 'sales5_id')->select(['id', 'nick_name', 'settle_tax_type']);
    }
    
    public function mcht()
    {
        return $this->belongsTo(Merchandise::class, 'mcht_id')->select([
            'id', 'mcht_name', 'user_name', 'nick_name',
            'addr', 'detail_addr', 'resident_num', 'business_num',
        ]);
    }
    // mcht
    public function getMchtNameAttribute()
    {
        return $this->mcht ? $this->mcht->mcht_name : null;
    }

    public function getNickNameAttribute()
    {
        return $this->mcht ? $this->mcht->mcht_name : null;
    }

    public function getAddrAttribute()
    {
        return $this->mcht ? $this->mcht->addr : null;
    }

    public function getDetailAddrAttribute()
    {
        return $this->mcht ? $this->mcht->detail_addr : null;
    }

    public function getResidentNumAttribute()
    {
        return $this->mcht ? $this->mcht->resident_num : null;
    }

    public function getbusinessNumAttribute()
    {
        return $this->mcht ? $this->mcht->business_num : null;
    }

    public function getUserNameAttribute()
    {
        return $this->mcht ? $this->mcht->user_name : null;
    }
    //
    public function getSales0NameAttribute()
    {
        return $this->sales0 ? $this->sales0->nick_name : null;
    }

    public function getSales1NameAttribute()
    {
        return $this->sales1 ? $this->sales1->nick_name : null;
    }

    public function getSales2NameAttribute()
    {
        return $this->sales2 ? $this->sales2->nick_name : null;
    }

    public function getSales3NameAttribute()
    {
        return $this->sales3 ? $this->sales3->nick_name : null;
    }
    
    public function getSales4NameAttribute()
    {
        return $this->sales4 ? $this->sales4->nick_name : null;
    }

    public function getSales5NameAttribute()
    {
        return $this->sales5 ? $this->sales5->nick_name : null;
    }
    //
    public function getTrxDttmAttribute()
    {
        return $this->trx_dt." ".$this->trx_tm;
    }
    
    public function getCxlDttmAttribute()
    {
        return $this->cxl_dt." ".$this->cxl_tm;
    }
    
    protected function apprNum() : Attribute
    {
        return new Attribute(
            get: fn ($value) => sprintf('%08d', $value),
        );
    }

    protected function mchtSettleFee() : Attribute
    {
        return new Attribute(
            get: fn ($value) => request()->level == 10 ? $value : 0,
        );
    }
}
