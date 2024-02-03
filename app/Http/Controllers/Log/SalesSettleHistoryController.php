<?php

namespace App\Http\Controllers\Log;

use Illuminate\Support\Facades\DB;
use App\Models\Log\SettleHistorySalesforce;
use App\Models\Transaction;
use App\Models\Salesforce;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Traits\Settle\SettleHistoryTrait;
use App\Http\Traits\Salesforce\UnderSalesTrait;
use App\Http\Requests\Manager\IndexRequest;
use App\Http\Requests\Manager\Log\CreateSettleHistoryRequest;
use App\Http\Requests\Manager\Log\BatchSettleHistoryRequest;

/**
 * @group Sales-Settle-History API
 *
 * 영업점 정산이력 API 입니다.
 */
class SalesSettleHistoryController extends Controller
{
    use ManagerTrait, ExtendResponseTrait, SettleHistoryTrait, UnderSalesTrait;
    protected $settle_sales_hist;
    
    public function __construct(SettleHistorySalesforce $settle_sales_hist)
    {
        $this->settle_sales_hist = $settle_sales_hist;
    }

    private function commonQuery($request)
    {
        $search = $request->input('search', '');
        $query  = $this->settle_sales_hist
                ->join('salesforces', 'settle_histories_salesforces.sales_id', 'salesforces.id')
                ->where('settle_histories_salesforces.brand_id', $request->user()->brand_id)
                ->where('settle_histories_salesforces.is_delete', false)
                ->where('salesforces.sales_name', 'like', "%$search%");

        if($request->has('s_dt'))
            $query = $query->where('settle_histories_salesforces.settle_dt', '>=', $request->s_dt);
        if($request->has('e_dt'))
            $query = $query->where('settle_histories_salesforces.settle_dt', '<=', $request->e_dt);
        if($request->has('deposit_status'))
            $query = $query->where('settle_histories_salesforces.deposit_status', $request->deposit_status);

        if(isSalesforce($request))
        {
            $sales_ids = $this->underSalesFilter($request);
            // 하위가 1000명이 넘으면 ..?
            $query = $query->whereIn('salesforces.id', $sales_ids);
        }
        if($request->has('level'))
            $query = $query->where('settle_histories_salesforces.level', $request->level);
        return $query;
    }

    public function chart(Request $request)
    {
        $query = $this->commonQuery($request);

        $total = $query->first([
            DB::raw("SUM(appr_amount) AS appr_amount"),
            DB::raw("SUM(cxl_amount) AS cxl_amount"),
            DB::raw("SUM(total_amount) AS total_amount"),
            DB::raw("SUM(trx_amount) AS trx_amount"),
            DB::raw("SUM(comm_settle_amount) AS comm_settle_amount"),
            DB::raw("SUM(under_sales_amount) AS under_sales_amount"),
            DB::raw("SUM(deduct_amount) AS deduct_amount"),
            DB::raw("SUM(settle_amount) AS settle_amount"),
        ]);
        return $this->response(0, $total);
    }

    public function index(IndexRequest $request)
    {
        $cols = ['salesforces.user_name', 'salesforces.sales_name', 'salesforces.level', 'settle_histories_salesforces.*'];
        $query = $this->commonQuery($request);
        $data = $this->getIndexData($request, $query, 'settle_histories_salesforces.id', $cols, 'settle_histories_salesforces.settle_dt', false);
        return $this->response(0, $data);
    }

    public function store(CreateSettleHistoryRequest $request)
    {
        return DB::transaction(function () use($request) {
            [$target_id, $target_settle_id] = $this->getTargetInfo($request->level);
            $query = Transaction::where($target_id, $request->id);
            return $this->createSalesforceCommon($request, $query, $target_settle_id);
        });
    }

    public function storePart(CreateSettleHistoryRequest $request)
    {
        return DB::transaction(function () use($request) {
            [$target_id, $target_settle_id] = $this->getTargetInfo($request->level);
            $query = Transaction::whereIn('id', $request->selected);
            return $this->createSalesforceCommon($request, $query, $target_settle_id);
        });
    }

    public function batch(BatchSettleHistoryRequest $request)
    {
        return DB::transaction(function () use($request) {
            $c_res = true;
            for ($i=0; $i < count($request->datas); $i++) 
            {
                [$target_id, $target_settle_id] = $this->getTargetInfo($request->level);
                $data = $request->data('sales_id', $request->datas[$i]);
                $data['level'] = $request->level;

                $query = Transaction::where($target_id, $data['sales_id']);
                $c_res = $this->settle_sales_hist->create($data);
                $u_res = $this->SetTransSettle($query, $target_settle_id, $c_res->id);
                $s_res = Salesforce::where('id', $request->id)->update(['last_settle_dt' => $data['settle_dt']]);
                $p_res = $this->SetPayModuleLastSettleMonth($data, $target_settle_id, $c_res->id);
            }
            return $this->response($c_res ? 1 : 990);
        });
    }

    public function destroy(Request $request, $id)
    {        
        if($request->use_finance_van_deposit && $request->current_status)
            return $this->extendResponse(2000, "입금완료된 정산건은 정산취소 할수 없습니다.");
        else
        {
            return DB::transaction(function () use($request, $id) {
                [$target_id, $target_settle_id] = $this->getTargetInfo($request->level);
                $res = $this->deleteSalesforceCommon($request, $id, $target_settle_id, 'sales_id');
                return $this->response($res ? 1 : 990, ['id'=>$id]);
            });    
        }
    }
    
    protected function createSalesforceCommon($request, $query, $target_settle_id)
    {
        $data = $request->data('sales_id');
        $data['level'] = $request->level;

        $c_res = $this->settle_sales_hist->create($data);
        $u_res = $this->SetTransSettle($query, $target_settle_id, $c_res->id);
        $s_res = Salesforce::where('id', $request->id)->update(['last_settle_dt' => $request->dt]);
        $p_res = $this->SetPayModuleLastSettleMonth($data, $target_settle_id, $c_res->id);

        return $this->response($c_res ? 1 : 990, ['id'=>$c_res->id]);
    }
    
    protected function deleteSalesforceCommon($request, $id, $target_settle_id, $user_id)
    {
        $query = $this->settle_sales_hist->where('id', $id);
        $hist  = $query->first()->toArray();
        if($hist)
        {
            $request = $request->merge(['id' => $id]);
            // 삭제시에는 거래건이 적용되기전, 먼저 반영되어야함
            $p_res = $this->RollbackPayModuleLastSettleMonth($hist, $target_settle_id);
            $u_res = $this->SetNullTransSettle($request, $target_settle_id);
            $d_res = $query->update(['is_delete' => true]);
            $s_res = Salesforce::where('id', $hist[$user_id])->update(['last_settle_dt' => null]);
            return $this->response(1);
        }
        else
            return $this->response(1000);
    }
    
    /**
     * 입금상태 변경
     */
    public function setDeposit(Request $request, $id)
    {
        if($request->user()->tokenCan(35))
        {
            if($request->use_finance_van_deposit)
            {   // 정산금 이체(실시간)
                if($request->current_status == 0)
                    $res = post($this->base_noti_url."/sales-settle-deposit/$id", ['brand_id'=> $request->brand_id, 'fin_id'=> $request->fin_id]);
                else
                    return $this->extendResponse(2000, "입금완료된 정산건은 다시 입금할 수 없습니다.");
            }
            if($res['body']['result_cd'] == '0000')
                return $this->deposit($this->settle_sales_hist, $id);
            else
                return $this->apiResponse($res['body']['result_cd'], $res['body']['result_msg']);
        }
        else
            return $this->response(951);
    }
}
