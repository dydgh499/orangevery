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

        $query = globalSalesFilter($query, $request, 'merchandises');
        $query = globalAuthFilter($query, $request, 'merchandises');
        return $query;
    }

    public function chart(Request $request)
    {
        $query = $this->commonQuery($request)
                ->where('settle_histories_merchandises.created_at', '>=', $request->s_dt)
                ->where('settle_histories_merchandises.created_at', '<=', $request->e_dt);

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

    public function index(IndexRequest $request)
    {
        $cols = ['merchandises.user_name', 'merchandises.mcht_name', 'settle_histories_merchandises.*'];
        $query = $this->commonQuery($request);
        $data = $this->getIndexData($request, $query, 'settle_histories_merchandises.id', $cols, 'settle_histories_merchandises.created_at');
        return $this->response(0, $data);
    }

    public function store(CreateSettleHistoryRequest $request)
    {
        return DB::transaction(function () use($request) {
            $query = Transaction::where('mcht_id', $request->id);
            return $this->createMerchandiseCommon($request, $query);
        });
    }

    public function storePart(CreateSettleHistoryRequest $request)
    {
        return DB::transaction(function () use($request) {
            $query = Transaction::whereIn('id', $request->selected);
            return $this->createMerchandiseCommon($request, $query);
        });
    }

    public function batch(BatchSettleHistoryRequest $request)
    {
        return DB::transaction(function () use($request) {
            $c_res = true;
            for ($i=0; $i < count($request->datas); $i++) 
            { 
                $data = $request->data('mcht_id', $request->datas[$i]);
                $data['settle_fee'] = $request->datas[$i]['settle_fee'];
                $data['cancel_deposit_amount'] = $request->datas[$i]['cancel_deposit_amount'];
                $data['collect_withdraw_amount'] = $request->datas[$i]['collect_withdraw_amount'];

                $query = Transaction::where('mcht_id', $data['mcht_id']);
                $c_res = $this->settle_mcht_hist->create($data);
                $u_res = $this->SetTransSettle($query, 'mcht_settle_id', $c_res->id);    
                $p_res = $this->SetPayModuleLastSettleMonth($data, 'mcht_settle_id', $c_res->id);
                $cw_res= $this->SetCollectWithdraw($data, $c_res->id);
            }
            return $this->response($c_res ? 1 : 990);    
        });
    }

    public function destroy(Request $request, $id)
    {
        return DB::transaction(function () use($request, $id) {
            $res = $this->deleteMchtforceCommon( $request, $id, 'mcht_id', 'mcht_settle_id', 'mcht_id');
            return $this->response($res ? 1 : 990, ['id'=>$id]);
        });
    }


    protected function createMerchandiseCommon($request, $query)
    {
        $data = $request->data('mcht_id');
        $data['settle_fee'] = $request->settle_fee;
        $data['cancel_deposit_amount']      = $request->cancel_deposit_amount;
        $data['collect_withdraw_amount']    = $request->collect_withdraw_amount;
        
        $c_res = $this->settle_mcht_hist->create($data);
        $u_res = $this->SetTransSettle($query, 'mcht_settle_id', $c_res->id);
        $p_res = $this->SetPayModuleLastSettleMonth($data, 'mcht_settle_id', $c_res->id);
        $cw_res= $this->SetCollectWithdraw($data, $c_res->id);

        return $this->response($c_res ? 1 : 990, ['id'=>$c_res->id]);
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
        return $this->deposit($this->settle_mcht_hist, $id);
    }

    /**
     * 재이체(실시간)
     */
    public function settleDeposit(Request $request)
    {
        $validated = $request->validate(['trx_id'=>'required', 'mid'=>'required', 'tid'=>'nullable']);
        $data = $request->all();
        $url = $this->base_noti_url.'/deposit';
        $res = post($url, $data);

        $code = $res['body']['result_cd'] == '0000' ? 1 : $res['body']['result_cd'];
        return $this->extendResponse($code, $res['body']['result_msg']);
    }

    /**
     * 모아서 출금(정산)
     */
    public function settleCollect(CreateSettleHistoryRequest $request)
    {
        $pay_module = PaymentModule::where('is_delete', false)
            ->where('mcht_id', $request->id)
            ->where('fin_id', '!=', 0)
            ->first();

        $data = $request->data('mcht_id');
        $data['settle_fee'] = $request->settle_fee;
        $data['trx_ids'] = $request->selected;
        $data['fin_id'] = $pay_module->fin_id;
        $url = $this->base_noti_url.'/settle-collect';
        $res = post($url, $data);
        return $this->response(1, $res['body']);
    }
}
