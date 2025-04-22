<?php

namespace App\Http\Traits\Settle;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

trait SettleTerminalTrait
{
    // 통신비 세팅
    protected function setSettleTerminals($data, $settle_e_dt)
    {
        $c_settle_e_dt = Carbon::parse($settle_e_dt)->copy();   //정산 종료일
        foreach($data['content'] as $content) 
        {
            $terminal = $content->terminal;
            $terminal['settle_pay_module_idxs'] = [];
            foreach($content->settlePayModules as $pay_module)
            {
                //개통일에 M + comm_settle_type을 적용. 0=개통월부터 적용, 1=M+1, 2=M+2
                $comm_settle_dt = Carbon::parse($pay_module->begin_dt)->addMonthNoOverflow($pay_module->comm_settle_type);
                $m_offset = 1;
                // 정산을 하지 않았을때 : 통신비 정산 시작월 부터 오프셋 계산
                if($pay_module->last_settle_month == 0) {
                    $m_offset += $c_settle_e_dt->diffInMonths($comm_settle_dt);
                }
                else
                {   // 정산을 했었을때: 마지막 정산월 부터 오프셋 계산
                    $year  = substr($pay_module->last_settle_month, 0, 4);
                    $month = substr($pay_module->last_settle_month, 4, 2);
                    $m_offset += ($c_settle_e_dt->diffInMonths("$year-$month-01") -1);
                }
                if($m_offset === 1)
                {   // 통신비가 1달만 밀렷을 때 (날짜비교)
                    if($pay_module->comm_settle_day > $c_settle_e_dt->day)
                    {   // 통신비를 내야하는 날이 아닐 때
                        $m_offset = 0;
                    }
                }
                $comm_total_amount = ($pay_module->comm_settle_fee * $m_offset);                
                $terminal['amount'] -= $comm_total_amount;
                if($comm_total_amount > 0)
                    $terminal['settle_pay_module_idxs'][] = $pay_module->id;
            }
            // 정산할 결제모듈 건수
            $content->terminal = $terminal;
        }
        return $data;
    }

    protected function getUnderSettleGroups($pay_modules, $settle_e_dt)
    {
        $c_settle_e_dt = Carbon::parse($settle_e_dt);
        $underSettleDivision = function ($under_sales_type) {
            return function ($pay_module) use ($under_sales_type) {
                return $pay_module->under_sales_type == $under_sales_type;
            };    
        };
        $getUnderSalesGroup = function($pmod_ids, $s_dt, $e_dt) {
                $query = Transaction::whereIn('pmod_id', $pmod_ids)
                    ->where('brand_id', request()->user()->brand_id)
                    ->whereRaw("transactions.trx_at >= ?", [$s_dt])
                    ->whereRaw("transactions.trx_at <= ?", [$e_dt]);
                return $under_sales = $query->groupby('pmod_id')
                        ->get([DB::raw('SUM(amount) AS total_amount'), 'pmod_id']);
        };
        //작월 1일 ~ 작월 말일
        $under_sales1 = $pay_modules->filter($underSettleDivision(1))->values();
        $pmod_ids1 = collect($under_sales1)->pluck('id')->all();
        if(count($pmod_ids1))
        {
            $mon_ago_1_s = $c_settle_e_dt->copy()->subMonthNoOverflow(1)->startOfMonth()->format('Y-m-d');
            $mon_ago_1_e = $c_settle_e_dt->copy()->subMonthNoOverflow(1)->endOfMonth()->format('Y-m-d');
            $group1 = $getUnderSalesGroup($pmod_ids1, $mon_ago_1_s, $mon_ago_1_e);
        }
        else
            $group1 = [];
        //D-30 ~ 정산일
        $under_sales2 = $pay_modules->filter($underSettleDivision(2))->values();
        $pmod_ids2 = collect($under_sales2)->pluck('id')->all();
        if(count($pmod_ids2))
        {
            $day_ago_30 = $c_settle_e_dt->copy()->subDays(30)->format('Y-m-d');
            $settle_day = $c_settle_e_dt->format('Y-m-d');
            $group2 = $getUnderSalesGroup($pmod_ids2, $day_ago_30, $settle_day);
        }
        else
            $group2 = [];
        return [$group1, $group2];
    }

    // 매출미달 차감금 세팅
    protected function setSettleUnderAmount($data, $settle_e_dt)
    {
        $setSettleUnderAmount = function($data, $groups) {
            $groups  = json_decode(json_encode($groups), true);
            foreach($data['content'] as $content) 
            {
                foreach($content->settlePayModules as $pay_module)
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
            foreach($content->settlePayModules as $pay_module)
            {
                $pay_modules[] = $pay_module;
            }
        }
        [$group1, $group2] = $this->getUnderSettleGroups(collect($pay_modules), $settle_e_dt);
        $data = $setSettleUnderAmount($data, $group1);
        $data = $setSettleUnderAmount($data, $group2);
        return $data;
    }
    
    protected function setTerminalCost($data, $settle_s_dt, $settle_e_dt, $target_id)
    {
        $data = $this->setSettleTerminals($data, $settle_e_dt);
        $data = $this->setSettleUnderAmount($data, $settle_e_dt);
        return $data;
    }
}
