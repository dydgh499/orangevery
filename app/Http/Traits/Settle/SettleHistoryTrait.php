<?php

namespace App\Http\Traits\Settle;
use App\Models\Transaction;
use App\Models\Salesforce;

trait SettleHistoryTrait
{
    protected function SetTransSettle($query, $target_settle_id, $resource_id)
    {
        return $query
            ->globalFilter()
            ->settleFilter($target_settle_id)
            ->settleTransaction()
            ->update([$target_settle_id => $resource_id]);
    }

    protected function SetNullTransSettle($request, $target_id, $target_settle_id, $user_id)
    {
        return Transaction::query()
            ->where($target_id, $user_id)
            ->nullSettleFilter($target_settle_id, $request->id)
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

    protected function createMerchandiseCommon($request, $query)
    {
        $data = $request->data('mcht_id');
        $data['settle_fee'] = $request->settle_fee;

        $c_res = $this->settle_mcht_hist->create($data);
        $u_res = $this->SetTransSettle($query, 'mcht_settle_id', $c_res->id);
        return $this->response($c_res && $u_res ? 1 : 990);
    }

    protected function createSalesforceCommon($request, $query, $target_settle_id)
    {
        $data = $request->data('sales_id');
        $data['level'] = $request->level;

        $c_res = $this->settle_sales_hist->create($data);
        $u_res = $this->SetTransSettle($query, $target_settle_id, $c_res->id);
        $s_res = Salesforce::where('id', $request->id)->update(['last_settle_dt' => $request->dt]);
        return $this->response($c_res && $u_res && $s_res ? 1 : 990);
    }


    protected function deleteMchtforceCommon($request, $id, $target_id, $target_settle_id, $user_id)
    {
        $query = $this->settle_mcht_hist->where('id', $id);
        $hist  = $query->first()->toArray();
        if($hist)
        {
            $request = $request->merge(['id' => $id]);
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
            $u_res = $this->SetNullTransSettle($request, $target_id, $target_settle_id, $hist[$user_id]);
            $d_res = $query->update(['is_delete' => true]);
            $s_res = Salesforce::where('id', $hist[$user_id])->update(['last_settle_dt' => null]);
            return $this->response(1);
        }
        else
            return $this->response(1000);
    }
}
