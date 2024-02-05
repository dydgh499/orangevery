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
        $data = $this->getIndexData($request, $query, 'settle_histories_merchandises.id', $cols, 'settle_histories_merchandises.settle_dt', false);
        return $this->response(0, $data);
    }

    /*
    * 정산이력추가 - 가맹점
    */
    public function store(CreateSettleHistoryRequest $request)
    {
        return DB::transaction(function () use($request) {
            $query = Transaction::where('mcht_id', $request->id);
            return $this->createMerchandiseCommon($request, $query);
        });
    }

    /*
    * 부분정산이력추가 - 가맹점
    */
    public function storePart(CreateSettleHistoryRequest $request)
    {
        return DB::transaction(function () use($request) {
            $query = Transaction::whereIn('id', $request->selected);
            return $this->createMerchandiseCommon($request, $query);
        });
    }

    /*
    * 정산이력 - 일괄정산 - 가맹점
    */
    public function batch(BatchSettleHistoryRequest $request)
    {
        for ($i=0; $i < count($request->datas); $i++) 
        { 
            $_data = $request->datas[$i];
            DB::transaction(function () use($request, $_data) {
                $data = $request->data('mcht_id', $_data);
                $data['settle_fee'] = $_data['settle_fee'];
                $data['cancel_deposit_amount'] = $_data['cancel_deposit_amount'];
                $data['collect_withdraw_amount'] = $_data['collect_withdraw_amount'];

                $query = Transaction::where('mcht_id', $data['mcht_id']);
                $c_res = $this->settle_mcht_hist->create($data);
                $u_res = $this->SetTransSettle($query, 'mcht_settle_id', $c_res->id);    
                $p_res = $this->SetPayModuleLastSettleMonth($data, 'mcht_settle_id', $c_res->id);
                $cw_res= $this->SetCollectWithdraw($data, $c_res->id);
                return true;
            });
        }
        return $this->response(1);    
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
            return DB::transaction(function () use($request, $id) {
                $res = $this->deleteMchtforceCommon($request, $id, 'mcht_settle_id');
                return $this->response($res ? 1 : 990, ['id'=>$id]);
            });    
        }
    }


    protected function createMerchandiseCommon($request, $query)
    {
        $cancel_deposit_amount = $request->cancel_deposit_amount;
        $collect_withdraw_amount = $request->collect_withdraw_amount;
        $data = $request->data('mcht_id');
        $data['settle_fee'] = $request->settle_fee;
        $data['cancel_deposit_amount']      = $cancel_deposit_amount ? $cancel_deposit_amount : 0;
        $data['collect_withdraw_amount']    = $collect_withdraw_amount ? $collect_withdraw_amount : 0;
        $c_res = $this->settle_mcht_hist->create($data);
        $u_res = $this->SetTransSettle($query, 'mcht_settle_id', $c_res->id);
        $p_res = $this->SetPayModuleLastSettleMonth($data, 'mcht_settle_id', $c_res->id);
        $cw_res= $this->SetCollectWithdraw($data, $c_res->id);

        return $this->response($c_res ? 1 : 990, ['id'=>$c_res->id]);
    }


    protected function deleteMchtforceCommon($request, $id, $target_settle_id)
    {
        $query = $this->settle_mcht_hist->where('id', $id);
        $hist  = $query->first()->toArray();
        if($hist)
        {
            $request = $request->merge(['id' => $id]);
            // 삭제시에는 거래건이 적용되기전, 먼저 반영되어야함
            $p_res = $this->RollbackPayModuleLastSettleMonth($hist, $target_settle_id);
            $u_res = $this->SetNullTransSettle($request, $target_settle_id);
            $cw_res= $this->SetNullCollectWithdraw($hist);
            $d_res = $query->update(['is_delete' => true]);
            return $this->response($d_res ? 1 : 990);    
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
