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
use App\Http\Controllers\Ablilty\Ablilty;
use App\Http\Controllers\Ablilty\EditAbleWorkTime;
use App\Http\Controllers\Manager\Service\BrandInfo;

use App\Http\Requests\Manager\IndexRequest;
use App\Http\Requests\Manager\Log\CreateSettleHistoryRequest;
use App\Http\Requests\Manager\Log\BatchSettleHistoryRequest;

use App\Http\Controllers\Manager\Salesforce\UnderSalesforce;
use App\Http\Controllers\Manager\Salesforce\SalesforceOverlap;

/**
 * @group Sales-Settle-History API
 *
 * 영업라인 정산이력 API 입니다.
 */
class SalesSettleHistoryController extends Controller
{
    use ManagerTrait, ExtendResponseTrait, SettleHistoryTrait;
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
        if($request->level)
            $query = $query->where('settle_histories_salesforces.level', $request->level);    

        $sales_filters = UnderSalesforce::getSelectedSalesFilter($request);
        if(count($sales_filters))
        {
            $query = $query->whereIn('settle_histories_salesforces.sales_id', UnderSalesforce::getSalesIds($request));
            $levels = UnderSalesforce::colToLevel($sales_filters);
            foreach($levels as $level)
            {
                $query = $query->where('settle_histories_salesforces.level', '<=', $level);
            }
        }
        return $query;
    }

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
            DB::raw("SUM(comm_settle_amount) AS comm_settle_amount"),
            DB::raw("SUM(under_sales_amount) AS under_sales_amount"),
            DB::raw("SUM(deduct_amount) AS deduct_amount"),
            DB::raw("SUM(settle_amount) AS settle_amount"),
            DB::raw("SUM(deposit_amount) AS deposit_amount"),
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
        $data = $this->getIndexData($request, $query, 'settle_histories_salesforces.id', $cols, 'settle_histories_salesforces.created_at', false);
        return $this->response(0, $data);
    }

    protected function createSalesforceCommon($item, $data, $target_settle_id)
    {
        $db_count = Transaction::whereIn('id', $item['settle_transaction_idxs'])->noSettlement($target_settle_id)->count();
        $data['level']  = $item['level'];
        $seltte_month   = date('Ym', strtotime($data['settle_dt']));

        if(count($item['settle_transaction_idxs']) === $db_count)
        {
            $c_res = $this->settle_sales_hist->create($data);
            if($c_res)
            {
                $chunks = array_chunk($item['settle_transaction_idxs'], 1000);
                foreach ($chunks as $chunk) {
                    $res = $this->SetTransSettle(Transaction::whereIn('id', $chunk), $target_settle_id, $c_res->id);
                }
                $this->SetPayModuleLastSettleMonth($item['settle_pay_module_idxs'], $seltte_month);    
                Salesforce::where('id', $item['id'])->update(['last_settle_dt' => $data['settle_dt']]);
                return $c_res->id;
            }
            else
                return false;    
        }
        else
            return -1;
    }

    /*
    * 정산이력추가
    */
    public function store(CreateSettleHistoryRequest $request)
    {
        $item = $request->all();
        $data = $request->data('sales_id');

        if(EditAbleWorkTime::validate() === false)
            return $this->extendResponse(1500, '지금은 작업할 수 없습니다.');
        
        $c_id = DB::transaction(function () use($item, $data) {
            [$target_id, $target_settle_id, $target_settle_amount] = getTargetInfo($item['level']);
            return $this->createSalesforceCommon($item, $data, $target_settle_id);
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
        $success_res = ['ids' => []];

        for ($i=0; $i < count($request->datas); $i++) 
        {
            $item = $request->datas[$i];
            $data = $request->data('sales_id', $item);

            $c_id = DB::transaction(function () use($item, $data) {
                [$target_id, $target_settle_id, $target_settle_amount] = getTargetInfo($item['level']);
                return $this->createSalesforceCommon($item, $data, $target_settle_id);
            });
            if($c_id === 0)
                $fail_res[] = '#'.$item['id'].' 영업라인이 정산에 실패했습니다.';
            else if($c_id === -1)
                $fail_res[] = '#'.$item['id'].' 이미 정산이 완료된 건입니다.';
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
    public function destroy(Request $request, int $id)
    {        
        if($request->use_finance_van_deposit && $request->current_status)
            return $this->extendResponse(2000, "입금완료, 상계처리된 정산건은 정산취소 할수 없습니다.");
        else
        {
            [$target_id, $target_settle_id, $target_settle_amount] = getTargetInfo($request->level);
            $result = DB::transaction(function () use($id, $target_settle_id) {
                $query = $this->settle_sales_hist->where('id', $id);
                $hist  = $query->first();
                if($hist)
                {
                    $hist = $hist->toArray();
                    // 삭제시에는 거래건이 적용되기전, 먼저 반영되어야함
                    $this->RollbackPayModuleLastSettleMonth($hist, $target_settle_id);
                    Salesforce::where('id', $hist['sales_id'])->update(['last_settle_dt' => null]);
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
                $this->SetNullTransSettle($id, $target_settle_id);
                logging(['end'=>date('Y-m-d H:i:s')]);    
            }
            return $this->response($result ? 1 : 1000, ['id' => $id]);
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

    /**
     * 추가차감
     */
    public function addDeduct(Request $request, int $id)
    {
        return $this->addDeductHistory($request, $id, $this->settle_sales_hist);
    }

    /**
     * 계좌정보 연동
     */
    public function linkAccount(Request $request, int $id)
    {
        $code = $this->linkAccountHistory($request, $id, $this->settle_sales_hist, new Salesforce);
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
            $code = $this->linkAccountHistory($request, $request->data[$i], $this->settle_sales_hist, new Salesforce);
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
        $res = $this->settle_sales_hist->whereIn('id', $request->data)->update([
            'deposit_status' => 2,
        ]);
        return $this->response(1);
    }
}
