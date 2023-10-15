<?php

namespace App\Http\Traits\Settle;
use App\Models\Transaction;
use App\Models\PaymentModule;
use App\Models\Salesforce;
use Carbon\Carbon;

trait SettleHistoryTrait
{
    protected function SetTransSettle($query, $target_settle_id, $resource_id)
    {
        return $query
            ->noSettlement($target_settle_id)
            ->update([$target_settle_id => $resource_id]);
    }

    protected function SetNullTransSettle($request, $target_id, $target_settle_id, $user_id)
    {
        return Transaction::query()
            ->where($target_id, $user_id)
            ->settleFilter($target_settle_id, $request->id)
            ->update([$target_settle_id => null]);
    }

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

    protected function createMerchandiseCommon($request, $query)
    {
        $data = $request->data('mcht_id');
        $data['settle_fee'] = $request->settle_fee;

        $c_res = $this->settle_mcht_hist->create($data);
        $u_res = $this->SetTransSettle($query, 'mcht_settle_id', $c_res->id);
        $p_res = $this->SetPayModuleLastSettleMonth($data, 'mcht_settle_id', $c_res->id);
        return $this->response($c_res ? 1 : 990);
    }

    protected function createSalesforceCommon($request, $query, $target_settle_id)
    {
        $data = $request->data('sales_id');
        $data['level'] = $request->level;

        $c_res = $this->settle_sales_hist->create($data);
        $u_res = $this->SetTransSettle($query, $target_settle_id, $c_res->id);
        $s_res = Salesforce::where('id', $request->id)->update(['last_settle_dt' => $request->dt]);
        $p_res = $this->SetPayModuleLastSettleMonth($data, $target_settle_id, $c_res->id);

        return $this->response($c_res ? 1 : 990);
    }


    protected function deleteMchtforceCommon($request, $id, $target_id, $target_settle_id, $user_id)
    {
        $query = $this->settle_mcht_hist->where('id', $id);
        $hist  = $query->first()->toArray();
        if($hist)
        {
            $request = $request->merge(['id' => $id]);
            // 삭제시에는 거래건이 적용되기전, 먼저 반영되어야함
            $p_res = $this->RollbackPayModuleLastSettleMonth($hist, $target_settle_id);
            $u_res = $this->SetNullTransSettle($request, $target_id, $target_settle_id, $hist[$user_id]);
            $d_res = $query->update(['is_delete' => true]);
            return $this->response($d_res ? 1 : 990);    
        }
        else
            return $this->response(1000);

    }

    protected function deleteSalesforceCommon($request, $id, $target_id, $target_settle_id, $user_id)
    {
        $query = $this->settle_sales_hist->where('id', $id);
        $hist  = $query->first()->toArray();
        if($hist)
        {
            $request = $request->merge(['id' => $id]);
            // 삭제시에는 거래건이 적용되기전, 먼저 반영되어야함
            $p_res = $this->RollbackPayModuleLastSettleMonth($hist, $target_settle_id);
            $u_res = $this->SetNullTransSettle($request, $target_id, $target_settle_id, $hist[$user_id]);
            $d_res = $query->update(['is_delete' => true]);
            $s_res = Salesforce::where('id', $hist[$user_id])->update(['last_settle_dt' => null]);
            return $this->response(1);
        }
        else
            return $this->response(1000);
    }
}
