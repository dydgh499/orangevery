<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Service\Holiday;
use App\Models\Salesforce;
use App\Models\Merchandise;
use App\Models\CancelDeposit;
use App\Models\CollectWithdraw;
use App\Models\Log\RealtimeSendHistory;

use Illuminate\Support\Facades\Redis;
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
        $query = $query
            ->where('transactions.is_delete', false)
            ->where('transactions.brand_id', request()->user()->brand_id);
        $query = globalPGFilter($query, request(), 'transactions');
        $query = globalSalesFilter($query, request(), 'transactions');
        $query = globalAuthFilter($query, request(), 'transactions');
        return $query;
    }

    static public function getHolidays($brand_id)
    {
        //조회일로부터 M-1, M+1 공휴일 조회
        $s_dt = Carbon::createFromFormat('Y-m-d', request()->s_dt)->copy()->subMonthNoOverflow(1)->startOfMonth()->format('Y-m-d');
        $e_dt = Carbon::createFromFormat('Y-m-d', request()->e_dt)->copy()->addMonthNoOverflow(1)->endOfMonth()->format('Y-m-d');
        $key_name = "holidays-".$brand_id."-$s_dt-$e_dt";

        $holidays = Redis::get($key_name);
        if($holidays == null)
        {
            $rest_dts = Holiday::where('brand_id', $brand_id)
                ->where("rest_dt", ">=", $s_dt)
                ->where("rest_dt", "<=", $e_dt)
                ->pluck('rest_dt')->all();
            if($rest_dts)
            {
                Redis::set($key_name, json_encode($rest_dts), 'EX', 300);
                $holidays = json_encode($rest_dts);
            }
            else
                $holidays = "";
        }

        $holidays = str_replace("[", "", $holidays);
        $holidays = str_replace("]", "", $holidays);
        $holidays = str_replace('"', "", $holidays);
        return $holidays;
    }

    // 정산 안한 것들 조회
    public function scopeNoSettlement($query, $target)
    {
        if(request()->is_base_trx)
        {
            $s_dt = strlen(request()->s_dt) === 10 ? date(request()->s_dt." 00:00:00") : request()->s_dt;
            $e_dt = strlen(request()->e_dt) === 10 ? date(request()->e_dt." 23:59:59") : request()->e_dt;
            $trx_dt = 'transactions.trx_at';
        }
        else
        {
            $s_dt = str_replace('-', '', request()->s_dt);
            $e_dt = str_replace('-', '', request()->e_dt);
            $trx_dt = 'transactions.settle_dt';
        }
        
        return $query->whereNull($target)
            ->whereRaw("$trx_dt >= ?", [$s_dt])
            ->whereRaw("$trx_dt <= ?", [$e_dt])
            ->globalFilter();
    }

    public function getTrxAmountAttribute()
    {   //거래 수수료(거래수수료 + 유보금 + 입금수수료)
        if(request()->level)
        {
            [$target_id, $target_settle_id, $target_settle_amount] = getTargetInfo(request()->level);
            if((int)request()->level === 10)
                return $this->amount - $this[$target_settle_amount] - $this->mcht_settle_fee;
            else
                return $this->amount - $this[$target_settle_amount];    
        }
        else
            return 0;
    }

    public function getTotalTrxAmountAttribute()
    {
        if(request()->level)
        {
            [$target_id, $target_settle_id, $target_settle_amount] = getTargetInfo(request()->level);
            if((int)request()->level === 50)
                return $this->amount - $this[$target_settle_amount] - $this->dev_realtime_settle_amount;
            else
                return $this->amount - $this[$target_settle_amount];
        }
        else
            return 0;
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
        return $this->hasMany(RealtimeSendHistory::class, 'trans_id')
            ->orderby('id', 'desc')
            ->select();
    }

    public function cancelDeposits()
    {   // is delete 없음
        return $this->hasMany(CancelDeposit::class, 'trans_id')
            ->orderby('id', 'desc')
            ->select();
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
    
    public function getResidentNumFrontAttribute()
    {
        $resident_num_front = '';
        if ($this->resident_num) {
            $resident_num = str_replace('-', '', $this->resident_num);            
            if (strlen($resident_num) >= 6)
                $resident_num_front = substr($resident_num, 0, 6);
            else
                $resident_num_front = $resident_num;
        }
        return $resident_num_front;
    }

    public function getResidentNumBackAttribute()
    {
        $resident_num_back = '';
        if ($this->resident_num) {
            $resident_num = str_replace('-', '', $this->resident_num);
            if (strlen($resident_num) > 6)
                $resident_num_back = substr($resident_num, 6);
        }
        return $resident_num_back;
    }
}
