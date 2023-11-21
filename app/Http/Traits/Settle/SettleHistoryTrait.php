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
    protected function SetNullTransSettle($request, $target_id, $target_settle_id, $user_id)
    {
        return Transaction::where('brand_id', request()->user()->brand_id)
            ->where($target_id, $user_id)
            ->where($target_settle_id, $request->id)
            ->update([$target_settle_id => null]);
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
            return $this->response($res ? 1 : 990);    
        }
        else
            return $this->response(1000);
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
