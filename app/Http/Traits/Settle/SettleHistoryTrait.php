<?php

namespace App\Http\Traits\Settle;
use App\Models\CollectWithdraw;
use App\Models\Transaction;
use App\Models\PaymentModule;
use App\Models\CancelDeposit;
use App\Models\Salesforce;
use Carbon\Carbon;

trait SettleHistoryTrait
{
    /*
    * 정산 - 거래건
    */
    protected function SetTransSettle($query, $target_settle_id, $resource_id)
    {
        return $query
            ->noSettlement($target_settle_id)
            ->update([$target_settle_id => $resource_id]);
    }

    /*
    * 정산취소 - 거래건
    */
    protected function SetNullTransSettle($request, $target_settle_id)
    {
        /*  2024-01-25 JYH
            정산 후, 가맹점, 영업점 변경한 경우 mcht_id -> user_id를 찾을 수 없음, 
            target_settle_id(mcht_settle_id, sales5_settle_id ..) -> id 로만 찾아야함
        */
        return Transaction::where('brand_id', request()->user()->brand_id)
            ->where($target_settle_id, $request->id)
            ->update([$target_settle_id => null]);
    }

    protected function getRequestResponseMessage($data, $messge)
    {
        return "(#".$data['id'].") ".$messge."\n";        
    }

    protected function depositContainer($request, $target, $data, $orm)
    {
        $getDBResponseMessage = function($code) {
            if($code === 1)
                return '';
            else if($code === 990)
                return $this->getRequestResponseMessage($data, "DB처리 실패");
            else if($code === 1000)
                return $this->getRequestResponseMessage($data, "이력을 찾을 수 없음");
            else
                return $this->getRequestResponseMessage($data, "알수 없는 응답");
        };

        if($request->use_finance_van_deposit)
        {   // 정산금 이체(실시간)
             if($data['current_status'] == 0)
             {
                $url = $this->base_noti_url."/$target-settle-deposit/".$data['id'];
                $params = ['brand_id'=> $request->brand_id, 'fin_id'=> $request->fin_id];

                $res = post($url, $params);
                sleep(0.1);
                if($res['body']['result_cd'] == '0000')
                    return $getDBResponseMessage($this->deposit($orm, $data['id']));
                else
                    return $this->getRequestResponseMessage($data ,$res['body']['result_msg']);
             }
             else
                 return $this->getRequestResponseMessage($data ,"입금완료된 정산건은 다시 입금할 수 없습니다.");
        }
        else
            return $getDBResponseMessage($this->deposit($orm, $data['id']));
    }

    /*
    * 이체하기 - 가맹점, 영업점 공통
    */
    protected function deposit($orm, $id)
    {
        $query = $orm->where('id', $id);
        $hist = $query->first();
        if($hist)
        {
            $deposit_dt     = $hist->deposit_status ? null : date('Y-m-d');
            $deposit_status = !$hist->deposit_status;
            $res = $query->update(['deposit_dt'=>$deposit_dt, 'deposit_status'=>$deposit_status]);
            return $res ? 1 : 990;
        }
        else
            return 1000;
    }

    /*
    * 정산 - 통신비
    */
    protected function SetPayModuleLastSettleMonth($settle_pay_module_idxs, $settle_month)
    {
        PaymentModule::whereIn('id', $settle_pay_module_idxs)->update(['last_settle_month' => $settle_month]);
    }

    /*
    * 정산 - 취소수기입금
    */
    protected function SetCancelDeposit($cancel_deposit_idxs, $settle_id)
    {
        CancelDeposit::whereIn('id', $cancel_deposit_idxs)->update(['mcht_settle_id' => $settle_id]);
    }
    
    /*
    * 정산 취소 - 취소수기입금
    */
    protected function SetNullCancelDeposit($data)
    {
        // FK delete NULL update
        // CancelDeposit::where('mcht_settle_id', $data['id'])->update(['mcht_settle_id' => null]);
    }
    
    protected function GetSettlePayModuleIds($data, $target_settle_id, $settle_id)
    {
        return Transaction::where('brand_id', $data['brand_id'])
            ->where($target_settle_id, $settle_id)
            ->distinct()
            ->pluck('pmod_id')
            ->all();
    }

    /*
    * 정산 취소 - 통신비
    */
    protected function RollbackPayModuleLastSettleMonth($data, $target_settle_id)
    {
        if($data['comm_settle_amount'] || $data['under_sales_amount'])
        {   //통신비나, 매출미달 차감금이 존재하면 해당 거래건에 포함된 결제모듈들 날짜 1달전으로 수정
            $settle_date = Carbon::createFromFormat('Y-m-d', $data['settle_dt']);
            $settle_date->subMonth();
            $settle_month = $settle_date->format('Ym');
            $pmod_ids = $this->GetSettlePayModuleIds($data, $target_settle_id, $data['id']);
            return PaymentModule::whereIn('id', $pmod_ids)->update(['last_settle_month' => $settle_month]);
        }
        return true;
    }

    public function getSettleHistoryData($request, $query, $parent, $cols=[])
    {
        $page      = $request->input('page');
        $page_size = $request->input('page_size');
        $sp = ($page - 1) * $page_size;
        $min    = $query->min("$parent.id");
        $res    = ['page'=>$page, 'page_size'=>$page_size];
        if($min)
        {
            $con_query = $query->where("$parent.id", '>=', $min);
            $res['total']   = $query->count();

            $con_query = $con_query->orderBy("$parent.created_at", 'desc')->offset($sp)->limit($page_size);
            $res['content'] = $con_query->get($cols);
        }
        else
        {
            $res['total'] = 0;
            $res['content'] = [];
        }
        return $res;
    }

    public function addDeductHistory($request, $id, $orm)
    {
        if($request->user()->tokenCan(35))
        {
            $history = $orm->where('id', $id)->first();
            if($history)
            {
                $history->deduct_amount += (int)$request->deduct_amount;
                $history->settle_amount -= (int)$request->deduct_amount;
                if($history->deposit_status)
                    $history->deposit_amount -= (int)$request->deduct_amount;
                $history->save();
                return $this->response(1);
            }
            else
                return $this->response(1000);
        }
        else
            return $this->response(951);
    }

    public function linkAccountHistory($request, $id, $orm, $user_orm)
    {
        if($request->user()->tokenCan(35))
        {
            $history = $orm->where('id', $id)->first();
            if($history)
            {
                $user = $user_orm->where('id', $history->mcht_id)->first([
                    'acct_name', 'acct_num', 'acct_bank_name', 'acct_bank_code',
                ]);
                if($user)
                {
                    $history->acct_name = $user->acct_name;
                    $history->acct_num = $user->acct_num;
                    $history->acct_bank_name = $user->acct_bank_name;
                    $history->acct_bank_code = $user->acct_bank_code;
                    $history->save();
                    return 1;
                }
                else
                    return 1000;
            }
            else
                return 1000;
        }
        else
            return 951;
    }
}
