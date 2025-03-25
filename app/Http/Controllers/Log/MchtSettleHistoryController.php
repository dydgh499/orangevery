<?php

namespace App\Http\Controllers\Log;

use Illuminate\Support\Facades\DB;
use App\Models\Log\SettleHistoryMerchandise;
use App\Models\Merchandise;
use App\Models\Transaction;
use App\Models\Merchandise\PaymentModule;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Traits\Settle\SettleHistoryTrait;
use App\Http\Requests\Manager\IndexRequest;
use App\Http\Requests\Manager\Log\CreateSettleHistoryRequest;
use App\Http\Requests\Manager\Log\BatchSettleHistoryRequest;
use App\Http\Controllers\Ablilty\Ablilty;
use App\Http\Controllers\Utils\Comm;

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
                /*
                ->rightJoin('payment_modules', 'settle_histories_merchandises.mcht_id', '=', 'payment_modules.mcht_id')
                ->where(function ($query) use($request) {
                    return globalPGFilter($query, $request, 'payment_modules');
                })
                */
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
            DB::raw("SUM(settle_histories_merchandises.settle_fee) AS settle_fee"),
            DB::raw("SUM(comm_settle_amount) AS comm_settle_amount"),
            DB::raw("SUM(under_sales_amount) AS under_sales_amount"),
            DB::raw("SUM(deduct_amount) AS deduct_amount"),
            DB::raw("SUM(cancel_deposit_amount) AS cancel_deposit_amount"),
            DB::raw("SUM(settle_amount) AS settle_amount"),
            DB::raw("SUM(deposit_amount) AS deposit_amount"),
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
        $data = $this->getIndexData($request, $query, 'settle_histories_merchandises.id', $cols, 'settle_histories_merchandises.created_at', false);
        return $this->response(0, $data);
    }

    protected function createMerchandiseCommon($item, $data, $target_settle_id)
    {
        $db_count = Transaction::whereIn('id', $item['settle_transaction_idxs'])->noSettlement($target_settle_id)->count();
        $data['settle_fee']                 = $item['settle_fee'] + $item['withdraw_fee'];
        $data['cancel_deposit_amount']      = $item['cancel_deposit_amount'] ? $item['cancel_deposit_amount'] : 0;
        $seltte_month = date('Ym', strtotime($data['settle_dt']));

        if(count($item['settle_transaction_idxs']) === $db_count)
        {
            $c_res = $this->settle_mcht_hist->create($data);
            if($c_res)
            {
                $chunks = array_chunk($item['settle_transaction_idxs'], 1000);
                foreach ($chunks as $chunk) {
                    $res = $this->SetTransSettle(Transaction::whereIn('id', $chunk), $target_settle_id, $c_res->id);
                }
                $this->SetPayModuleLastSettleMonth($item['settle_pay_module_idxs'], $seltte_month);   
                $this->SetCancelDeposit($item['cancel_deposit_idxs'], $c_res->id);
                return $c_res->id;
            }
            else
                return 0;    
        }
        else
            return -1;
    }

    /*
    * 정산이력추가 - 가맹점
    */
    public function store(CreateSettleHistoryRequest $request)
    {
        $item = $request->all();
        $data = $request->data('mcht_id');

        $c_id = DB::transaction(function () use($item, $data) {
            return $this->createMerchandiseCommon($item, $data, 'mcht_settle_id');
        });
        if($c_id)
            return $this->response(1, ['id'=>$c_id]);
        else if($c_id === 0)
            return $this->response(990);
        else if($c_id === -1)
            return $this->extendResponse(2000, '이미 정산이 완료된 건입니다.');
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
                return $this->createMerchandiseCommon($item, $data, 'mcht_settle_id');
            });
            if($c_id === 0)
                $fail_res[] = '#'.$item['id'].' 가맹점이 정산에 실패했습니다.';
            else if($c_id === -1)
                $fail_res[] = '#'.$item['id'].' 이미 정산이 완료된 건입니다.';
            else
                $success_res['ids'][] = $c_id;
        }
        if(count($fail_res))
        {
            $message = "일괄작업에 실패한 정산건들이 존재합니다.<br><br>".json_encode($fail_res, JSON_UNESCAPED_UNICODE);
            return $this->extendResponse(2000, $message);
        }
        else
            return $this->response(1, $success_res);
    }

    /*
    * 정산이력 - 정산취소
    */
    public function destroy(Request $request, int $id)
    {
        if($request->use_finance_van_deposit && $request->current_status)
            return $this->extendResponse(2000, "입금완료, 상계처리된 정산건은 정산취소 할수 없습니다.");
        else
        {
            [$target_id, $target_settle_id, $target_settle_amount] = getTargetInfo(10);
            $result = DB::transaction(function () use($id, $target_settle_id) {
                $query = $this->settle_mcht_hist->where('id', $id);
                $hist  = $query->first();
                if($hist)
                {
                    $hist = $hist->toArray();
                    // 삭제시에는 거래건이 적용되기전, 먼저 반영되어야함
                    $this->RollbackPayModuleLastSettleMonth($hist, $target_settle_id);
                    $this->SetNullCancelDeposit($hist);
                    $this->SetNullTransSettle($id, $target_settle_id);
                    $query->delete();
                    return true;
                }
                else
                    return false;
            });
            return $this->response($code ? 1 : 1000, ['id' => $id]);
        }
    }

    /**
     * 입금상태 변경
     */
    public function setDeposit(Request $request, int $id)
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
            $validated = $request->validate(['trx_id'=>'required', 'pmod_id'=>'required']);
            $data = $request->all();
            $url = $this->base_noti_url.'/single-deposit';
            $res = Comm::post($url, $data);
            return $this->apiResponse($res['body']['result_cd'], $res['body']['result_msg']);
        }
        else
            return $this->response(951);
    }

    /**
     * 추가차감
     */
    public function addDeduct(Request $request, int $id)
    {
        return $this->addDeductHistory($request, $id, $this->settle_mcht_hist);
    }

    /**
     * 계좌정보 연동
     */
    public function linkAccount(Request $request, int $id)
    {
        $code = $this->linkAccountHistory($request, $id, $this->settle_mcht_hist, new Merchandise);
        return $this->response($code);
    }

    /*
    * 정산이력 - 일괄정산
    */
    public function batchLinkAccount(Request $request)
    {
        $fail_res = [];
        for ($i=0; $i < count($request->data); $i++) 
        {
            $code = $this->linkAccountHistory($request, $request->data[$i], $this->settle_mcht_hist, new Merchandise);
            if($code !== 1)
                array_push($fail_res, '#'.$request->data[$i]);
        }
        if(count($fail_res))
        {
            $message = "일괄작업에 실패한 이력들이 존재합니다.\n\n".json_encode($fail_res, JSON_UNESCAPED_UNICODE);
            return $this->extendResponse(2000, $message);
        }
        else
            return $this->response(1);
    }

    /*
    * 정산이력 - 상계처리
    */
    public function batchOffsetProcess(Request $request)
    {
        $res = $this->settle_mcht_hist->whereIn('id', $request->data)->update([
            'deposit_status' => 2,
        ]);
        return $this->response(1);
    }
}
