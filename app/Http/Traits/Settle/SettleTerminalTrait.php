<?php

namespace App\Http\Traits\Settle;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

trait SettleTerminalTrait
{
    // 통신비 세팅
    protected function setSettleTerminals($data, $settle_dt)
    {
        $c_settle_dt = Carbon::parse($settle_dt)->copy();
        foreach($data['content'] as $content) 
        {
            foreach($content->pay_modules as $pay_module)
            {
                //개통일에 M + comm_settle_type을 적용. 0=개통월부터 적용, 1=M+1, 2=M+2
                $comm_settle_able_dt = Carbon::parse($pay_module->begin_dt)->addMonths($pay_module->comm_settle_type);
                $terminal = $content->terminal;
                if($c_settle_dt->gte($comm_settle_able_dt))
                {
                    $terminal['amount'] -= $pay_module->comm_settle_fee;
                }

                $content->terminal = $terminal;
            }
        }
        return $data;
    }

    protected function getUnderSettleGroups($pay_modules, $settle_dt)
    {
        $c_settle_dt = Carbon::parse($settle_dt);
        $underSettleDivision = function ($under_sales_type) {
            return function ($pay_module) use ($under_sales_type) {
                return $pay_module->under_sales_type == $under_sales_type;
            };    
        };
        $getUnderSalesGroup = function($pmod_ids, $s_dt, $e_dt) {
            return $under_sales = Transaction::whereIn('pmod_id', $pmod_ids)
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
                ->groupby('pmod_id')
                ->get([DB::raw('SUM(amount) AS total_amount'), 'pmod_id']);
        };
        //작월 1일 ~ 작월 말일
        $under_sales1 = $pay_modules->filter($underSettleDivision(1))->values();
        $pmod_ids1 = collect($under_sales1)->pluck('id')->all();
        if(count($pmod_ids1))
        {
            $mon_ago_1_s = $c_settle_dt->copy()->subMonths(1)->startOfMonth()->format('Y-m-d');
            $mon_ago_1_e = $c_settle_dt->copy()->subMonths(1)->endOfMonth()->format('Y-m-d');
            $group1 = $getUnderSalesGroup($pmod_ids1, $mon_ago_1_s, $mon_ago_1_e);
        }
        else
            $group1 = [];
        //D-30 ~ 정산일
        $under_sales2 = $pay_modules->filter($underSettleDivision(2))->values();
        $pmod_ids2 = collect($under_sales2)->pluck('id')->all();
        if(count($pmod_ids2))
        {
            $day_ago_30 = $c_settle_dt->copy()->subDays(30)->format('Y-m-d');
            $settle_day = $c_settle_dt->format('Y-m-d');
            $group2 = $getUnderSalesGroup($pmod_ids2, $day_ago_30, $settle_day);
        }
        else
            $group2 = [];
        return [$group1, $group2];
    }

    // 매출미달 차감금 세팅
    protected function setSettleUnderAmount($data, $settle_dt)
    {
        $setSettleUnderAmount = function($data, $groups) {
            $groups  = json_decode(json_encode($groups), true);
            foreach($data['content'] as $content) 
            {
                foreach($content->pay_modules as $pay_module)
                {   //결제모듈 ID <-> 실 매출합계 검색
                    $idx = array_search($pay_module->id, array_column($groups, 'pmod_id'));
                    if($idx !== false)
                    {   // 실 매출합계 <-> 매출미달차감금 비교
                        $under_sales_limit  = $pay_module->under_sales_limit * 10000;
                        if($under_sales_limit > $groups[$idx]['total_amount'])
                        {
                            $terminal = $content->terminal;
                            $terminal['under_sales_amount'] -= $pay_module->under_sales_amt;
                            $content->terminal = $terminal;
                        }
                    }
                }
            }
            return $data;
        };

        $pay_modules = [];
        foreach($data['content'] as $content) 
        {
            foreach($content->pay_modules as $pay_module)
            {
                $pay_modules[] = $pay_module;
            }
        }
        [$group1, $group2] = $this->getUnderSettleGroups(collect($pay_modules), $settle_dt);
        $data = $setSettleUnderAmount($data, $group1);
        $data = $setSettleUnderAmount($data, $group2);
        return $data;
    }
    
    protected function setTerminalCost($data, $pay_modules, $settle_dt, $target_id)
    {
        foreach($data['content'] as $content)
        {
            $id = $content->id;
            $content->pay_modules = $pay_modules->filter(function ($pay_module) use($id, $target_id) {
                return $pay_module[$target_id] == $id;
            })->values();
        }
        $data = $this->setSettleTerminals($data, $settle_dt);
        $data = $this->setSettleUnderAmount($data, $settle_dt);
        foreach($data['content'] as $content)
        {
            $content->makeHidden(['pay_modules']);
        }
        return $data;
    }
}
