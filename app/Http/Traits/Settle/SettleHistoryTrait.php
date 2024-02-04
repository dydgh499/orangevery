<?php

namespace App\Http\Traits\Settle;
use App\Models\CollectWithdraw;
use App\Models\Transaction;
use App\Models\PaymentModule;
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

    protected function depositContainer($request, $target, $data, $orm)
    {
        $getRequestResponseMessage = function($data, $messge) {
            return "(#".$data['id'].") ".$messge."\n";
        };
        
        $getDBResponseMessage = function($code) {
            if($code === 1)
                return '';
            else if($code === 990)
                return $getRequestResponseMessage($data, "DB처리 실패");
            else if($code === 1000)
                return $getRequestResponseMessage($data, "이력을 찾을 수 없음");
            else
                return $getRequestResponseMessage($data, "알수 없는 응답");
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
                    return $getRequestResponseMessage($data ,$res['body']['result_msg']);
             }
             else
                 return $getRequestResponseMessage($data ,"입금완료된 정산건은 다시 입금할 수 없습니다.");
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

    protected function getTargetInfo($level)
    {   //SettleTrait와 같은 함수 존재
        $idx = globalLevelByIndex($level);
        $target_id =  'sales'.$idx.'_id';
        $target_settle_id =  'sales'.$idx.'_settle_id';
        return [$target_id, $target_settle_id];
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
    * 정산 - 통신비
    */
    protected function SetPayModuleLastSettleMonth($data, $target_settle_id, $settle_id)
    {
        if($settle_id && ($data['comm_settle_amount'] || $data['under_sales_amount']))
        {   //통신비나, 매출미달 차감금이 존재하면 해당 거래건에 포함된 결제모듈들 날짜 업데이트
            $settle_month = date('Ym', strtotime($data['settle_dt']));
            $pmod_ids = $this->GetSettlePayModuleIds($data, $target_settle_id, $settle_id);
            return PaymentModule::whereIn('id', $pmod_ids)->update(['last_settle_month' => $settle_month]);
        }
        return true;
    }

    /*
    * 정산 - 모아서출금합계
    */
    protected function SetCollectWithdraw($data, $settle_id)
    {
        if($data['collect_withdraw_amount'])
        {
            return CollectWithdraw::where('mcht_id', $data['mcht_id'])
                    ->whereNull('mcht_settle_id')
                    ->update(['mcht_settle_id' => $settle_id]);
        }
        return true;
    }

    /*
    * 정산 취소 - 모아서출금합계
    */
    protected function SetNullCollectWithdraw($data)
    {
        if($data['collect_withdraw_amount'])
        {
            return CollectWithdraw::where('mcht_id', $data['mcht_id'])
                    ->where('mcht_settle_id', $data['id'])
                    ->update(['mcht_settle_id' => null]);
        }
        return true;
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
}
