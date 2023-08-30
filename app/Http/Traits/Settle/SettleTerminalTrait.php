<?php

namespace App\Http\Traits\Settle;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

trait SettleTerminalTrait
{
    // 통신비 세팅
    protected function setSettleTerminals($query, $data, $level, $settle_dt)
    {
        $settle_day = date('d', strtotime($settle_dt));
        $pay_modules = collect(
            $query->where('comm_settle_day', $settle_day)
            ->where('comm_calc_level', $level)
            ->where('begin_dt', '<', $settle_dt)
            ->get()
        );
        foreach($data['content'] as $content) 
        {
            $module = $pay_modules->firstWhere('mcht_id', $content->id);
            if($module)
            {
                $terminal = $content->terminal;
                $terminal['amount'] += $module->comm_settle_fee;
                $content->terminal = $terminal;
            }
        }
        return [$data, $pay_modules];
    }

    // 매출미달 차감금 세팅
    protected function setSettleUnderAmount($data, $pay_modules, $settle_key, $settle_dt)
    {
        $underSettleDivision = function ($under_sales_type) {
            return function ($pay_module) use ($under_sales_type) {
                return $pay_module->under_sales_type == $under_sales_type;
            };    
        };
        $getUnderSalesGroup = function($pmod_ids, $s_dt, $e_dt, $settle_key) {
            return $under_sales = Transaction::query()
                ->whereIn('pmod_id', $pmod_ids)
                ->where(function($query) use ($s_dt, $e_dt) {
                    $query->where(function($query) use($s_dt, $e_dt) {
                        $query->where('is_cancel', false)
                            ->where('trx_dt', '>=', $s_dt)
                            ->where('trx_dt', '<=', $e_dt);
                    })->orWhere(function($query) use($s_dt, $e_dt) {
                        $query->where('is_cancel', true)
                            ->where('cxl_dt', '>=', $s_dt)
                            ->where('cxl_dt', '<=', $e_dt);
                    });
                })
                ->groupby($settle_key)
                ->get([DB::raw('SUM(amount) AS amount'), $settle_key]);
        };
        $c_settle_dt = Carbon::parse($settle_dt);
        //작월 1일 ~ 작월 말일
        $under_sales1 = $pay_modules->filter($underSettleDivision(1))->values();
        $pmod_ids1 = collect($under_sales1)->pluck('id')->all();
        //D-30 ~ 정산일
        $under_sales2 = $pay_modules->filter($underSettleDivision(2))->values();
        $pmod_ids2 = collect($under_sales2)->pluck('id')->all();

        if(count($pmod_ids1))
        {
            $mon_ago_1_s = $c_settle_dt->copy()->subMonths(1)->startOfMonth()->format('Y-m-d');
            $mon_ago_1_e = $c_settle_dt->copy()->subMonths(1)->endOfMonth()->format('Y-m-d');
            $group1 = $getUnderSalesGroup($pmod_ids1, $mon_ago_1_s, $mon_ago_1_e, $settle_key);
        }
        if(count($pmod_ids2))
        {
            $day_ago_30 = $c_settle_dt->copy()->subDays(30)->format('Y-m-d');
            $settle_day = $c_settle_dt->format('Y-m-d');
            $group2 = $getUnderSalesGroup($pmod_ids2, $day_ago_30, $settle_day, $settle_key);
        }
        return $data;
    }
}
