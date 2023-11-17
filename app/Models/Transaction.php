<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Salesforce;
use App\Models\Merchandise;
use App\Models\CancelDeposit;
use App\Models\CollectWithdraw;
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

    public function scopeSettleDateTypeTransaction($query)
    {
        $s_dt = request()->s_dt;
        $e_dt = request()->e_dt;
        if(request()->input('is_base_trx', 'false') == 'true')
        {
            $trx_dt = 'transactions.trx_dt';
            $cxl_dt = 'transactions.cxl_dt';
        }
        else
        {
            $trx_dt = "AddBaseWorkingDays(transactions.trx_dt, transactions.mcht_settle_type+1, transactions.pg_settle_type)";
            $cxl_dt = "AddBaseWorkingDays(transactions.cxl_dt, transactions.mcht_settle_type+1, transactions.pg_settle_type)";
        }
        return $query->where(function ($query) use ($s_dt, $e_dt, $trx_dt) {     
                $query->whereRaw("$trx_dt >= '$s_dt'")
                    ->whereRaw("$trx_dt <= '$e_dt'")
                    ->where('transactions.is_cancel', false);
            })->orWhere(function ($query) use ($s_dt, $e_dt ,$cxl_dt) {
                $query->whereRaw("$cxl_dt >= '$s_dt'")
                    ->whereRaw("$cxl_dt <= '$e_dt'")
                    ->where('transactions.is_cancel', true);
            });
    }

    public function scopeNoSettlement($query, $target)
    {
        return $query->whereNull($target)
            ->globalFilter()
            ->settleDateTypeTransaction();
    }

    public function scopeSettleFilter($query, $target, $id)
    {
        return $query->where($target, $id)
            ->where('brand_id', request()->user()->brand_id);
    }

    public function scopeDateFilter($query)
    {   // s_dt, e_dt, use_search_date_detail
        $request = request();
        if($request->has('s_dt') && $request->has('e_dt'))
        {
            $query = $query->where(function($query) use($request) {
                $query->where(function($query) use($request) {
                    $search_format = $request->use_search_date_detail ? "concat(trx_dt, ' ', trx_tm)" : "trx_dt";
                    $query->where('transactions.is_cancel', false)
                        ->whereRaw("$search_format >= ?", [$request->s_dt])
                        ->whereRaw("$search_format <= ?", [$request->e_dt]);
                })->orWhere(function($query) use($request) {
                    $search_format = $request->use_search_date_detail ? "concat(cxl_dt, ' ', cxl_tm)" : "cxl_dt";
                    $query->where('transactions.is_cancel', true)
                    ->whereRaw("$search_format >= ?", [$request->s_dt])
                    ->whereRaw("$search_format <= ?", [$request->e_dt]);
                });
            });
        }
        return $query;
    }

    private function getProfitCol($level)
    {
        if($level == 10)
            $settle_key = 'mcht_settle_amount';
        else if($level < 35)
            $settle_key = 'sales'.globalLevelByIndex($level).'_settle_amount';
        else
        {
            $settle = $level == 50 ? 'dev' :'brand';
            $settle_key = $settle."_settle_amount";
        }
        return $settle_key;
    }

    public function getTrxAmountAttribute()
    {   //거래 수수료(거래수수료 + 유보금 + 입금수수료)
        $level = request()->level;
        $settle_key = $this->getProfitCol($level);

        if($level == 10)
            return $this->amount - $this[$settle_key] - $this->mcht_settle_fee;
        else
            return $this->amount - $this[$settle_key];
    }

    public function getTotalTrxAmountAttribute()
    {
        $level = request()->level;
        $settle_key = $this->getProfitCol($level);
        if($level == 50)
            return $this->amount - $this[$settle_key] - $this->dev_realtime_settle_amount;
        else
            return $this->amount - $this[$settle_key];
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
    {
        return $this->hasMany(CancelDeposit::class, 'trans_id')
            ->orderby('id', 'desc')
            ->select();
    }

    public function collectWithdraw()
    {
        return $this->hasMany(CollectWithdraw::class, 'mcht_id', 'mcht_id')
            ->whereNotNull('mcht_settle_id')
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
}
