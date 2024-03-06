<?php

namespace App\Http\Controllers\Log;

use Illuminate\Support\Facades\DB;
use App\Models\Log\SettleHistoryMerchandise;
use App\Models\Transaction;
use App\Models\PaymentModule;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Traits\Settle\SettleHistoryTrait;
use App\Http\Requests\Manager\IndexRequest;
use App\Http\Requests\Manager\Log\CreateSettleHistoryRequest;
use App\Http\Requests\Manager\Log\BatchSettleHistoryRequest;

/**
 * @group Merchandise-Settle-History API
 *
 * 가맹점 정산이력 API 입니다.
 */
class MchtSettleHistoryController extends Controller
{
    use ManagerTrait, ExtendResponseTrait, SettleHistoryTrait;
    protected $settle_mcht_hist;
    
    public function __construct(SettleHistoryMerchandise $settle_mcht_hist)
    {
        $this->settle_mcht_hist = $settle_mcht_hist;
        $this->base_noti_url = env('NOTI_URL', 'http://localhost:81').'/api/v2/realtimes';
    }

    private function commonQuery($request)
    {
        $search = $request->input('search', '');
        $query  = $this->settle_mcht_hist
                ->join('merchandises', 'settle_histories_merchandises.mcht_id', 'merchandises.id')
                ->where('settle_histories_merchandises.brand_id', $request->user()->brand_id)
                ->where('settle_histories_merchandises.is_delete', false)
                ->where('merchandises.mcht_name', 'like', "%$search%");

        if($request->has('s_dt'))
            $query = $query->where('settle_histories_merchandises.settle_dt', '>=', $request->s_dt);
        if($request->has('e_dt'))
            $query = $query->where('settle_histories_merchandises.settle_dt', '<=', $request->e_dt);
        if($request->has('deposit_status'))
            $query = $query->where('settle_histories_merchandises.deposit_status', $request->deposit_status);

        $query = globalSalesFilter($query, $request, 'merchandises');
        $query = globalAuthFilter($query, $request, 'merchandises');
        return $query;
    }

    /*
    * 정산이력 - 차트
    */
    public function chart(Request $request)
    {
        $query = $this->commonQuery($request);
        $total = $query->first([
            DB::raw("SUM(appr_count) AS appr_count"),
            DB::raw("SUM(cxl_count) AS cxl_count"),
            DB::raw("SUM(appr_amount) AS appr_amount"),
            DB::raw("SUM(cxl_amount) AS cxl_amount"),
            DB::raw("SUM(total_amount) AS total_amount"),
            DB::raw("SUM(trx_amount) AS trx_amount"),
            DB::raw("SUM(settle_fee) AS settle_fee"),
            DB::raw("SUM(comm_settle_amount) AS comm_settle_amount"),
            DB::raw("SUM(under_sales_amount) AS under_sales_amount"),
            DB::raw("SUM(deduct_amount) AS deduct_amount"),
            DB::raw("SUM(cancel_deposit_amount) AS cancel_deposit_amount"),
            DB::raw("SUM(collect_withdraw_amount) AS collect_withdraw_amount"),
            DB::raw("SUM(settle_amount) AS settle_amount"),
        ]);
        return $this->response(0, $total);
    }

    /*
    * 정산이력 - 가맹점
    */
    public function index(IndexRequest $request)
    {
        $cols = ['merchandises.user_name', 'merchandises.mcht_name', 'settle_histories_merchandises.*'];
        $query = $this->commonQuery($request);
        $data = $this->getSettleHistoryData($request, $query, 'settle_histories_merchandises', $cols);
        return $this->response(0, $data);
    }

    protected function createMerchandiseCommon($item, $data, $query)
    {
        $data['settle_fee']                 = $item['settle_fee'];
        $data['cancel_deposit_amount']      = $item['cancel_deposit_amount'] ? $item['cancel_deposit_amount'] : 0;
        $data['collect_withdraw_amount']    = $item['collect_withdraw_amount'] ? $item['collect_withdraw_amount'] : 0;
        $seltte_month = date('Ym', strtotime($data['settle_dt']));

        $c_res = $this->settle_mcht_hist->create($data);
        if($c_res)
        {
            $this->SetTransSettle($query, 'mcht_settle_id', $c_res->id);
            $this->SetPayModuleLastSettleMonth($item['settle_pay_module_idxs'], $seltte_month);   
            $this->SetCollectWithdraw($data, $c_res->id);    
            return $c_res->id;
        }
        else
            return false;

        return $this->response($c_res ? 1 : 990, ['id'=>$c_res->id]);
    }

    /*
    * 정산이력추가 - 가맹점
    */
    public function store(CreateSettleHistoryRequest $request)
    {
        $item = $request->all();
        $data = $request->data('mcht_id');

        $c_id = DB::transaction(function () use($item, $data) {
            $query = Transaction::whereIn('id', $item['settle_transaction_idxs']);
            return $this->createMerchandiseCommon($item, $data, $query);
        });
        return $this->response($c_id ? 1 : 990, ['id'=>$c_id]);
    }
    
    /*
    * 정산이력 - 일괄정산
    */
    public function batch(BatchSettleHistoryRequest $request)
    {
        $fail_res    = [];
        $success_res = ['ids'=>[]];

        for ($i=0; $i < count($request->datas); $i++) 
        { 
            $item = $request->datas[$i];
            $data = $request->data('mcht_id', $item);

            $c_id = DB::transaction(function () use($item, $data) {
                $query = Transaction::whereIn('id', $item['settle_transaction_idxs']);
                return $this->createMerchandiseCommon($item, $data, $query);
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
            $code = $this->deleteMchtforceCommon($request, $id, 'mcht_settle_id');
            return $this->response($code, ['id'=>$id]);
        }
    }


    protected function deleteMchtforceCommon($request, $id, $target_settle_id)
    {
        $result = DB::transaction(function () use($request, $id, $target_settle_id) {
            $query = $this->settle_mcht_hist->where('id', $id);
            $hist  = $query->first();
            if($hist)
            {
                $hist = $hist->toArray();
                $request = $request->merge(['id' => $id]);
                // 삭제시에는 거래건이 적용되기전, 먼저 반영되어야함
                $this->RollbackPayModuleLastSettleMonth($hist, $target_settle_id);
                $this->SetNullCollectWithdraw($hist);
                $query->delete();
                return true;
            }
            else
                return false;
        });
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
            $result = $this->depositContainer($request, 'mcht', $data, $this->settle_mcht_hist);
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
                $result = $this->depositContainer($request, 'mcht', $data, $this->settle_mcht_hist);
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
    
    /**
     * 즉시출금 단건 재이체(실시간)
     */
    public function singleDeposit(Request $request)
    {
        if($request->user()->tokenCan(35))
        {
            $validated = $request->validate(['trx_id'=>'required', 'mid'=>'required', 'tid'=>'nullable']);
            $data = $request->all();
            $url = $this->base_noti_url.'/single-deposit';
            $res = post($url, $data);
            return $this->apiResponse($res['body']['result_cd'], $res['body']['result_msg']);
        }
        else
            return $this->response(951);
    }
}
