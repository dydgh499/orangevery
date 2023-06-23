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
        'user_name', 'mcht_name',
        'trx_dttm', 'cxl_dttm',
    ];
    protected   $hidden     = [
        'sales0','sales1',
        'sales2','sales3',
        'sales4','sales5',
        'mcht', 'trx_dt', 'trx_tm', 'cxl_dt', 'cxl_tm'
    ];
    protected   $guarded    = [];

    public function getSalesProfit($type, $s_idx)
    {
        //가맹점, 하위대리점, 대리점, 하위총판, 총판, 하위지사, 지사
        $sales  = ['mcht', 'sales0', 'sales1', 'sales2', 'sales3', 'sales4', 'sales5'];
        $id     = $type."_id";
        $fee    = $type."_fee";

        for ($i=$s_idx; count($sales)-1 >= 0; $i--) 
        { 
            $sales_id   = $sales[$i]."_id";
            $sales_fee  = $sales[$i]."_fee";
            if($sales_id != 0)
                return $this->amount * ($this[$fee] - $this[$sales_fee]);
        }
        return 0;
    }

    public function getProfit($level)
    {
        if($level == 10)
        {   // 가맹점
            $profit = $this->amount - ($this->amount * ($this->mcht_fee + $this->hold_fee));
            $profit -= $this->settle_fee;
        }
        else if($level > 10 && $level < 31)
        {   // 영업자 
            $idx = globalLevelByIndex($level);
            $profit = $this->getSalesProfit('sales'.$idx, $idx);

            $property = 'sales'.$idx;
            $settle_tax_type = $this[$property]->settle_tax_type;

            if($settle_tax_type == 1)
                $profit *= 0.967;
            else if($settle_tax_type == 2)
                $profit *= 0.9;
            else if($settle_tax_type == 3)
            {
                $profit *= 0.9;
                $profit *= 0.967;
            }
        }
        else
        {   // 본사 
            $profit = $this->getSalesProfit('ps', 6);
            $dev_profit = $profit * $this->dev_fee;
            if($level == 50)
                $profit = $dev_profit;
            else if($level == 40)
                $profit -= $dev_profit;
        }
        return round($profit);
    }
    //
    public function getProfitAttribute()
    {
        return $this->getProfit(request()->level);
    }

    public function getTrxAmountAttribute()
    {
        return $this->amount - $this->profit;
    }
    
    public function getHoldAmountAttribute()
    {
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
        return $this->belongsTo(Merchandise::class, 'mcht_id')->select(['id', 'mcht_name', 'user_name']);
    }

    public function getMchtNameAttribute()
    {
        return $this->mcht ? $this->mcht->mcht_name : null;
    }

    public function getUserNameAttribute()
    {
        return $this->mcht ? $this->mcht->user_name : null;
    }

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
}
