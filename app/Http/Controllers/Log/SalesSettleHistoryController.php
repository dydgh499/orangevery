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

    /*
    * 정산이력
    */
    public function index(IndexRequest $request)
    {
        $cols = ['salesforces.user_name', 'salesforces.sales_name', 'salesforces.level', 'settle_histories_salesforces.*'];
        $query = $this->commonQuery($request);
        $data = $this->getSettleHistoryData($request, $query, 'settle_histories_salesforces', $cols);
        return $this->response(0, $data);
    }

    protected function createSalesforceCommon($item, $data, $query)
    {
        $data['level']  = $item['level'];
        $seltte_month   = date('Ym', strtotime($data['settle_dt']));
        [$target_id, $target_settle_id] = $this->getTargetInfo($item['level']);

        $c_res = $this->settle_sales_hist->create($data);
        if($c_res)
        {
            $this->SetTransSettle($query, $target_settle_id, $c_res->id);
            $this->SetPayModuleLastSettleMonth($item['settle_pay_module_idxs'], $seltte_month);    
            Salesforce::where('id', $item['id'])->update(['last_settle_dt' => $data['settle_dt']]);
            return $c_res->id;
        }
        else
            return false;
    }

    /*
    * 정산이력추가
    */
    public function store(CreateSettleHistoryRequest $request)
    {
        $item = $request->all();
        $data = $request->data('sales_id');

        $c_id = DB::transaction(function () use($item, $data) {
            $query = Transaction::whereIn('id', $item['settle_transaction_idxs']);
            return $this->createSalesforceCommon($item, $data, $query);
        });
        return $this->response($c_id ? 1 : 990, ['id'=>$c_id]);
    }

    /*
    * 정산이력 - 일괄정산 
    */
    public function batch(BatchSettleHistoryRequest $request)
    {
        $fail_res    = [];
        $success_res = ['ids'];
        for ($i=0; $i < count($request->datas); $i++) 
        {
            $item = $request->datas[$i];
            $data = $request->data('sales_id', $item);

            $c_id = DB::transaction(function () use($item, $data) {
                $query = Transaction::whereIn('id', $item['settle_transaction_idxs']);
                return $this->createSalesforceCommon($item, $data, $query);             
            });
            if($c_id === false)
                $fail_res[] = '#'.$item['id'].' 영업점이 정산에 실패했습니다.';
            else
                $success_res['ids'][] = $c_id;
        }

        if(count($fail_res))
        {
            $message = "일괄작업에 실패한 정산건들이 존재합니다.\n\n".json_encode($fail_res, JSON_UNESCAPED_UNICODE);
            return $this->extendResponse(2000, $message);
        }
        else
            return $this->response(1, $success_res);
    }
    
    /*
    * 정산이력 - 정산취소
    */
    public function destroy(Request $request, $id)
    {        
        if($request->use_finance_van_deposit && $request->current_status)
            return $this->extendResponse(2000, "입금완료된 정산건은 정산취소 할수 없습니다.");
        else
        {
            $code = $this->deleteSalesforceCommon($request, $id, 'sales_id');
            return $this->response($code, ['id'=>$id]);
        }
    }
    
    protected function deleteSalesforceCommon($request, $id, $user_id)
    {
        [$target_id, $target_settle_id] = $this->getTargetInfo($request->level);
        $result = DB::transaction(function () use($request, $id, $target_settle_id, $user_id) {
            $query = $this->settle_sales_hist->where('id', $id);
            $hist  = $query->first()->toArray();
            if($hist)
            {
                $request = $request->merge(['id' => $id]);
                // 삭제시에는 거래건이 적용되기전, 먼저 반영되어야함
                $this->RollbackPayModuleLastSettleMonth($hist, $target_settle_id);
                Salesforce::where('id', $hist[$user_id])->update(['last_settle_dt' => null]);
                $query->delete();
                return true;
            }
            else
                return false;
        });
        if($result)
        {
            logging(['start'=>date('Y-m-d H:i:s')]);
            //  Lock wait timeout exceeded; try restarting transaction
            $this->SetNullTransSettle($request, $target_settle_id);
            logging(['end'=>date('Y-m-d H:i:s')]);    
        }
        return $result;

    }
    
    /**
     * 입금상태 변경
     */
    public function setDeposit(Request $request, $id)
    {
        if($request->user()->tokenCan(35))
        {
            $data = ['id'=>$id, 'current_status'=>$request->current_status];
            $result = $this->depositContainer($request, 'sales', $data, $this->settle_sales_hist);
            if($result !== '')
            {
                $message = json_encode($result, JSON_UNESCAPED_UNICODE);
                return $this->extendResponse(2000, $message);
            }
            else
                return $this->response(1);
        }
        else
            return $this->response(951);
    }

    /**
    * 입금상태 변경
    */
    public function setBatchDeposit(Request $request)
    {
        $fail_res = [];
        if($request->user()->tokenCan(35))
        {
            for ($i=0; $i < count($request->data); $i++) 
            {
                $data = $request->data[$i];
                $result = $this->depositContainer($request, 'sales', $data, $this->settle_sales_hist);
                if($result !== '')
                    array_push($fail_res, $result);
            }
            if(count($fail_res))
            {
                $message = "일괄작업에 실패한 이체건들이 존재합니다.\n\n".json_encode($fail_res, JSON_UNESCAPED_UNICODE);
                return $this->extendResponse(2000, $message);
            }
            else
                return $this->response(1);
        }
        else
            return $this->response(951);
    }
}
