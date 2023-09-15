<?php

namespace App\Http\Controllers\Manager;

use App\Models\Transaction;
use App\Models\Salesforce;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Traits\Settle\TransactionTrait;
use App\Http\Requests\Manager\TransactionRequest;
use App\Http\Requests\Manager\IndexRequest;
use Illuminate\Database\QueryException;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Enums\HistoryType;

class TransactionController extends Controller
{
    use ManagerTrait, ExtendResponseTrait, TransactionTrait;
    protected $transactions;
    protected $target;
    public $cols;
    
    public function __construct(Transaction $transactions)
    {
        $this->transactions = $transactions;
        $this->target = '매출';
        $this->cols = [
            'merchandises.mcht_name', 'merchandises.user_name', 'merchandises.nick_name',
            'merchandises.addr', 'merchandises.resident_num', 'merchandises.business_num', 
            'merchandises.use_saleslip_prov', 'merchandises.use_saleslip_sell', 'merchandises.is_show_fee',
            'transactions.*',
            'payment_modules.note',
            DB::raw("concat(trx_dt, ' ', trx_tm) AS trx_dttm"),
            DB::raw("concat(cxl_dt, ' ', cxl_tm) AS cxl_dttm"),
        ];
    }

    protected function getTransactionData($request, $query)
    {
        [$settle_key, $group_key] = $this->getSettleCol($request);
        array_push($this->cols, $settle_key." AS profit");

        $page      = $request->input('page');
        $page_size = $request->input('page_size');
        $sp = ($page - 1) * $page_size;

        $min    = $query->min('transactions.id');
        $res    = ['page'=>$page, 'page_size'=>$page_size];
        if($min != NULL)
        {
            $con_query = $query->where('transactions.id', '>=', $min);
            $res['total']   = $query->count();
            $con_query = $con_query
                    ->orderBy('trx_dttm', 'desc')
                    ->orderBy('cxl_dttm', 'desc')
                    ->offset($sp)
                    ->limit($page_size);
            $res['content'] = $con_query->get($this->cols);
        }
        else
        {
            $res['total'] = 0;
            $res['content'] = [];
        }
        return $res;
    }

    public function commonSelect($request)
    {
        $search = $request->input('search', '');
        $query  = $this->transactions
            ->join('payment_modules', 'transactions.pmod_id', '=', 'payment_modules.id')
            ->join('merchandises', 'transactions.mcht_id', '=', 'merchandises.id')
            ->where('transactions.brand_id', $request->user()->brand_id)
            ->where('transactions.is_delete', false);

        if($search != '')
        {
            $query = $query->where(function ($query) use ($search) {
                return $query->where('transactions.mid', 'like', "%$search%")
                    ->orWhere('transactions.tid', 'like', "%$search%")
                    ->orWhere('transactions.trx_id', 'like', "%$search%")
                    ->orWhere('transactions.appr_num', 'like', "%$search%")
                    ->orWhere('payment_modules.note', 'like', "%$search%")
                    ->orWhere('merchandises.mcht_name', 'like', "%$search%")
                    ->orWhere('merchandises.resident_num', 'like', "%$search%")
                    ->orWhere('merchandises.business_num', 'like', "%$search%");
            });
        }
            
        if($request->has('s_dt') && $request->has('e_dt'))
        {
            $query = $query->where(function($query) use($request) {
                $query->where(function($query) use($request) {
                    $query->where('transactions.is_cancel', false)
                        ->where('transactions.trx_dt', '>=', $request->s_dt)
                        ->where('transactions.trx_dt', '<=', $request->e_dt);
                })->orWhere(function($query) use($request) {
                    $query->where('transactions.is_cancel', true)
                        ->where('transactions.cxl_dt', '>=', $request->s_dt)
                        ->where('transactions.cxl_dt', '<=', $request->e_dt);
                });
            });
            $request->query->remove('s_dt');
            $request->query->remove('e_dt');
        }
        $query = globalPGFilter($query, $request, 'transactions');
        $query = globalSalesFilter($query, $request, 'transactions');
        $query = globalAuthFilter($query, $request, 'transactions');

        if($request->has('mcht_settle_id'))
            $query = $query->where('transactions.mcht_settle_id', $request->mcht_settle_id);

        for ($i=0; $i < 6; $i++) { 
            $col = 'transactions.sales'.$i.'_settle_id';
            if($request->has($col))
                $query = $query->where($col, $request->input($col));
        }

        if($request->is_use_realtime_deposit && $request->level == 10)
            $query = $query->with(['realtimes']);
        return $query;
    }

    /**
     * 차트 데이터 출력
     *
     * 가맹점 이상 가능
     */
    public function chart(IndexRequest $request)
    {
        $query  = $this->commonSelect($request);
        [$settle_key, $group_key] = $this->getSettleCol($request);
        $cols = $this->getTotalCols($settle_key);
        $chart = $query->first($cols);
        $chart = $this->setTransChartFormat($chart);
        return $this->response(0, $chart);
    }

    /**
     * 목록출력
     *
     * 가맹점 이상 가능
     */
    public function index(IndexRequest $request)
    {
        $query = $this->commonSelect($request);
        $data   = $this->getTransactionData($request, $query);
        $sales_ids      = globalGetUniqueIdsBySalesIds($data['content']);
        $salesforces    = globalGetSalesByIds($sales_ids);
        $data['content'] = globalMappingSales($salesforces, $data['content']);
        return $this->response(0, $data);
    }

    /**
     * 추가
     *
     * 대리점 이상 가능
     *
     * @bodyParam user_pw string 유저 패스워드
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(TransactionRequest $request)
    {
        try
        {
            $data = $request->data();
            [$data] = $this->setSettleAmount([$data], $request->dev_settle_type);
            $res = $this->transactions->create($data);
            operLogging(HistoryType::CREATE, $this->target, $data, "#".$res->id);
            return $this->response($res ? 1 : 990, ['id'=>$res->id]);
        }
        catch(QueryException $ex)
        {
            $msg = $ex->getMessage();
            if(str_contains($msg, 'Duplicate entry'))
                $msg = '이미 같은 거래번호의 취소매출이 존재합니다.<br>해당 매출을 삭제하거나 거래번호를 수정한 후 다시 시도해주세요.';                
            return $this->extendResponse(990, $msg);
        }
    }

    /**
     * 단일조회
     *
     * 가맹점 이상 가능
     *
     * @urlParam id integer required 유저 PK
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, $id)
    {
        $data = $this->transactions->where('id', $id)->first();
        if($data)
        {
            $data->ps_fee = number_format($data->ps_fee * 100, 3);
            $data->mcht_fee = number_format($data->mcht_fee * 100, 3);
            $data->hold_fee = number_format($data->hold_fee * 100, 3);
            $data->sales5_fee = number_format($data->sales5_fee * 100, 3);
            $data->sales4_fee = number_format($data->sales4_fee * 100, 3);
            $data->sales3_fee = number_format($data->sales3_fee * 100, 3);
            $data->sales2_fee = number_format($data->sales2_fee * 100, 3);
            $data->sales1_fee = number_format($data->sales1_fee * 100, 3);
            $data->sales0_fee = number_format($data->sales0_fee * 100, 3);
        }
        else
            return $this->response(1000);
        return $data ? $this->response(0, $data) : $this->response(1000);
    }

    /**
     * 업데이트
     *
     * 가맹점 이상 가능
     *
     * @urlParam id integer required 유저 PK
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(TransactionRequest $request, $id)
    {
        try
        {
            $data = $request->data();
            [$data] = $this->setSettleAmount([$data], $request->dev_settle_type);
            $res = $this->transactions->where('id', $id)->update($data);
            operLogging(HistoryType::UPDATE, $this->target, $data, "#".$id);
            return $this->response($res ? 1 : 990);    
        }
        catch(QueryException $ex)
        {
            $msg = $ex->getMessage();
            if(str_contains($msg, 'Duplicate entry'))
                $msg = '이미 같은 거래번호의 취소매출이 존재합니다.<br>해당 매출을 삭제하거나 거래번호를 수정한 후 다시 시도해주세요.';                
            return $this->extendResponse(990, $msg);
        }
    }

    /**
     * 단일삭제
     *
     * @urlParam id integer required 유저 PK
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, $id)
    {
        if($this->authCheck($request->user(), $id, 35))
        {
            $res = $this->transactions->where('id', $id)->delete();
            operLogging(HistoryType::DELETE, $this->target, ['id' => $id], "#".$id);
            return $this->response(4);
        }
        else
            return $this->response(951);
    }

    /**
     * 취소매출 생성
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancel(TransactionRequest $request)
    {
        $data = $request->data();
        // TransactionRequest 에서 100을 먼저 나눠서 가져오기 떄문에 다시가져옴
        $data['ps_fee']  = $request->input('ps_fee', 0);
        $data['hold_fee']  = $request->input('hold_fee', 0);
        $data['mcht_fee']    = $request->input('mcht_fee', 0);
        $data['sales0_fee'] = $request->input('sales0_fee', 0);
        $data['sales1_fee'] = $request->input('sales1_fee', 0);
        $data['sales2_fee'] = $request->input('sales2_fee', 0);
        $data['sales3_fee'] = $request->input('sales3_fee', 0);
        $data['sales4_fee'] = $request->input('sales4_fee', 0);
        $data['sales5_fee'] = $request->input('sales5_fee', 0);
        try 
        {
            [$data] = $this->setSettleAmount([$data], $request->dev_settle_type);
            $res = $this->transactions->create($data);
            operLogging(HistoryType::CREATE, $this->target, $data, "#".$res->id);
            return $this->response(1);
        }
        catch(QueryException $ex)
        {
            $msg = $ex->getMessage();
            if(str_contains($msg, 'Duplicate entry'))
                $msg = '이미 같은 거래번호의 취소매출이 존재합니다.<br>해당 매출을 삭제하거나 거래번호를 수정한 후 다시 시도해주세요.';     
                
            return $this->extendResponse(990, $msg);
        }
    }

    /**
     * 수기결제
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function handPay(Request $request)
    {
        $getYYMM = function($mmyy) {
            if(mb_strlen($mmyy, 'utf-8') == 4)
            {
                $first 	= substr($mmyy, 0, 2);
                $sec 	= substr($mmyy, 2, 2);
                return $sec.$first;
            }
            else
                return '';
        };

        $data = $request->all();
        $data['yymm'] = $getYYMM($data['yymm']);
        $url = env('NOTI_URL', 'http://localhost:81').'/api/v2/online/pay/hand';
        $res = post($url, $data);
        if($res['body']['result_cd'] === "0000")
            return $this->response(1, $res['body']);
        else
            return $this->extendResponse(1999, $res['body']['result_msg']);
      
    }

    /**
     * 결제취소
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function payCancel(Request $request)
    {
        $data = $request->all();
        $url = env('NOTI_URL', 'http://localhost:81').'/api/v2/online/pay/cancel';
        $res = post($url, $data);
        if($res['body']['result_cd'] === "0000")
            return $this->response(1, $res['body']);
        else
            return $this->extendResponse(1999, $res['body']['result_msg']);
    }

    /*
     * 노티 대량 재전송
     */
    public function batchRetry(Request $request)
    {
        $url = env('NOTI_URL', 'http://localhost:81').'/api/v2/noti/custom';
        $trans = $this->transactions->whereIn('id', $request->selected)->get();
        foreach($trans as $tran)
        {
            $res = $this->notiSender($url, $tran, '');
        }
        return $this->response(1);
    }

    public function _test()
    {
        $dev_settle_type = 1;
        $db_trans = $this->transactions
            ->where('brand_id', 8)
            ->where('trx_dt', '>=', '2023-09-15')
            ->update(['dev_fee'=>0.1]);

        $db_trans = $this->transactions
            ->where('brand_id', 8)
            ->where('trx_dt', '>=', '2023-08-31')
            ->orderBy('transactions.id', 'desc')
            ->get();
        
        $trans = json_decode(json_encode($db_trans), true);
        $trans = $this->setSettleAmount($trans, $dev_settle_type);
        $i=0;
        foreach($db_trans as $tran)
        {
            $tran->brand_settle_amount = $trans[$i]['brand_settle_amount'];
            $tran->dev_settle_amount = $trans[$i]['dev_settle_amount'];
            $tran->mcht_settle_amount = $trans[$i]['mcht_settle_amount'];
            $tran->sales0_settle_amount = $trans[$i]['sales0_settle_amount'];
            $tran->sales1_settle_amount = $trans[$i]['sales1_settle_amount'];
            $tran->sales2_settle_amount = $trans[$i]['sales2_settle_amount'];
            $tran->sales3_settle_amount = $trans[$i]['sales3_settle_amount'];
            $tran->sales4_settle_amount = $trans[$i]['sales4_settle_amount'];
            $tran->sales5_settle_amount = $trans[$i]['sales5_settle_amount'];
            $tran->save();
            $i++;
        }
    }
    static public function test()
    {
        $ist = new TransactionController(new Transaction);
        $ist->_test();
    }
}
