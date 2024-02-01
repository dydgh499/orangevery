<?php

namespace App\Http\Controllers\Manager;

use App\Models\Transaction;
use App\Models\Salesforce;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Traits\Settle\TransactionTrait;
use App\Http\Requests\Manager\TransactionRequest;
use App\Http\Requests\Manager\IndexRequest;
use App\Http\Controllers\QuickView\QuickViewController;

use Carbon\Carbon;
use App\Enums\DevSettleType;
use Illuminate\Database\QueryException;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Enums\HistoryType;

/**
 * @group Transaction API
 *
 * 거래 API 입니다.
 */
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
            'merchandises.addr', 'merchandises.resident_num', 'merchandises.business_num', 'merchandises.tax_category_type',
            'merchandises.use_saleslip_prov', 'merchandises.use_saleslip_sell', 'merchandises.use_collect_withdraw', 'merchandises.is_show_fee',
            'transactions.*',
            'payment_modules.note', 'payment_modules.use_realtime_deposit', 'payment_modules.cxl_type', 'payment_modules.fin_trx_delay',
            DB::raw("concat(trx_dt, ' ', trx_tm) AS trx_dttm"),
            DB::raw("concat(cxl_dt, ' ', cxl_tm) AS cxl_dttm"),
        ];
    }

    public function getTransactionData($request, $query)
    {
        [$settle_key, $group_key] = $this->getSettleCol($request);
        if($settle_key == 'dev_settle_amount')
            $profit = DB::raw("($settle_key + dev_realtime_settle_amount) AS profit");
        else
            $profit = "$settle_key AS profit";
        array_push($this->cols, $profit);

        $page      = $request->input('page');
        $page_size = $request->input('page_size');
        return $this->transPagenation($query, 'transactions', $this->cols, $page, $page_size);
    }

    public function optionFilter($query, $request)
    {
        if($request->only_cancel)
            $query = $query->where('transactions.is_cancel', true);
        if($request->mcht_settle_id)
            $query = $query->where('transactions.mcht_settle_id', $request->mcht_settle_id);
        if($request->only_realtime_fail)
        {
            $trx_ids = $query->pluck('transactions.id')->all();
            $query = $query->failRealtime($trx_ids);            
        }
        if($request->no_settlement)
        {
            if($request->level == 10)
                $settle_key = 'mcht_settle_id';
            else if($request->level <= 30)
            {
                $idx = globalLevelByIndex($request->level);
                $settle_key = 'sales'.$idx.'_settle_id';
            }
            else
                $settle_key = 'dev_settle_id';
            $query = $query->whereNull("transactions.$settle_key");
        }

        for ($i=0; $i < 6; $i++) 
        {
            $col = 'sales'.$i.'_settle_id';
            if($request->has($col))
                $query = $query->where('transactions.'.$col, $request->input($col));
        }
        return $query;
    }

    public function commonSelect($request)
    {
        $search = $request->input('search', '');
        $query  = $this->transactions
            ->join('payment_modules', 'transactions.pmod_id', '=', 'payment_modules.id')
            ->join('merchandises', 'transactions.mcht_id', '=', 'merchandises.id')
            ->globalFilter();
        if($search !== "")
        {
            $query = $query->where(function ($query) use ($search) {
                return $query->where('transactions.mid', 'like', "%$search%")
                    ->orWhere('transactions.tid', 'like', "%$search%")
                    ->orWhere('transactions.appr_num', 'like', "%$search%")
                    ->orWhere('transactions.issuer', 'like', "%$search%")
                    ->orWhere('transactions.acquirer', 'like', "%$search%")
                    ->orWhere('transactions.buyer_phone', 'like', "%$search%")
                    ->orWhere('merchandises.mcht_name', 'like', "%$search%")
                    ->orWhere('merchandises.resident_num', 'like', "%$search%")
                    ->orWhere('merchandises.business_num', 'like', "%$search%")
                    ->orWhere('transactions.trx_id', $search)
                    ->orWhere('payment_modules.note', $search);
            });
        }
        $query = $this->transDateFilter($query, $request->s_dt, $request->e_dt, $request->use_search_date_detail);
        return $this->optionFilter($query ,$request);
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
        $with  = [];
        $query = $this->commonSelect($request);
        if($request->use_realtime_deposit && $request->level == 10)
            $with[] = 'realtimes';
        if($request->use_cancel_deposit)
            $with[] = 'cancelDeposits';
        if(count($with))
            $query = $query->with($with);

        $data   = $this->getTransactionData($request, $query);
        $sales_ids      = globalGetUniqueIdsBySalesIds($data['content']);
        $salesforces    = globalGetSalesByIds($sales_ids);
        $data['content'] = globalMappingSales($salesforces, $data['content']);
        
        foreach($data['content'] as $content)
        {
            $content->append(['resident_num_front', 'resident_num_back']);
            $content->setHidden(['resident_num']);
        }

        return $this->response(0, $data);
    }

    /**
     * 추가
     *
     * 대리점 이상 가능
     *
     * @bodyParam user_pw string 유저 패스워드
     */
    public function store(TransactionRequest $request)
    {
        try
        {
            $data = $request->data();
            $data['dev_fee'] = $request->input('dev_fee', 0)/100;
            $data['dev_realtime_fee'] = $request->input('dev_realtime_fee', 0)/100;
            if($data['dev_fee'] >= 1)
                return $this->extendResponse(991, '개발사 수수료가 이상합니다.<br>관리자에게 문의하세요.');
            else
            {
                [$data] = $this->setSettleAmount([$data], $request->dev_settle_type);
                $res = $this->transactions->create($data);
                operLogging(HistoryType::CREATE, $this->target, $data, "#".$res->id);
                return $this->response($res ? 1 : 990, ['id'=>$res->id]);    
            }
        }
        catch(QueryException $ex)
        {
            $msg = $ex->getMessage();
            if(str_contains($msg, 'Duplicate entry'))
                $msg = '이미 같은 거래번호의 취소매출이 존재합니다.<br>해당 매출을 삭제하거나 거래번호를 수정한 후 다시 시도해주세요.';                
            return $this->extendResponse(991, $msg);
        }
    }

    /**
     * 단일조회
     *
     * 가맹점 이상 가능
     *
     * @urlParam id integer required 유저 PK
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
            $data->dev_fee    = number_format($data->dev_fee * 100, 3);
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
     */
    public function update(TransactionRequest $request, $id)
    {
        try
        {
            $tran = $this->transactions->where('id', $id)->first();
            $data = $request->data();
            $data['dev_fee'] = $tran->dev_fee;
            $data['dev_realtime_fee'] = $tran->dev_realtime_fee;

            [$data] = $this->setSettleAmount([$data], $request->dev_settle_type);
            $res = $this->transactions->where('id', $id)->update($data);
            operLogging(HistoryType::UPDATE, $this->target, $data, "#".$id);
            return $this->response($res ? 1 : 990, ['id'=>$id]);
        }
        catch(QueryException $ex)
        {
            $msg = $ex->getMessage();
            if(str_contains($msg, 'Duplicate entry'))
                $msg = '이미 같은 거래번호의 취소매출이 존재합니다.<br>해당 매출을 삭제하거나 거래번호를 수정한 후 다시 시도해주세요.';                
            return $this->extendResponse(991, $msg);
        }
    }

    /**
     * 단일삭제
     *
     * @urlParam id integer required 유저 PK
     */
    public function destroy(Request $request, $id)
    {
        if($this->authCheck($request->user(), $id, 35))
        {
            $res = $this->transactions->where('id', $id)->delete();
            operLogging(HistoryType::DELETE, $this->target, ['id' => $id], "#".$id);
            return $this->response($res ? 1 : 990, ['id'=>$id]);
        }
        else
            return $this->response(951);
    }

    /**
     * 취소매출 생성
     *
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
                
            return $this->extendResponse(991, $msg);
        }
    }

    /**
     * 수기결제
     *
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
        $data['yymm'] = $getYYMM($data['yymm']); // mmyy to yymm
        $url = env('NOTI_URL', 'http://localhost:81').'/api/v2/online/pay/hand';
        $res = post($url, $data);
        if($res['body']['result_cd'] === "0000")
        {
            $data = $res['body'];
            unset($data['result_cd']);
            unset($data['result_msg']);
            unset($data['temp']);
            return $this->response(1, $data);
        }
        else
            return $this->extendResponse(1999, $res['body']['result_msg']);
    }

    /**
     * 결제취소
     *
     */
    public function payCancel(Request $request)
    {
        $data = $request->all();
        $res = post(env('NOTI_URL', 'http://localhost:81').'/api/v2/online/pay/cancel', $data);
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
        $url = env('NOTI_URL', 'http://localhost:81').'/api/v2/noti/payvery';
        $trans = $this->transactions->whereIn('id', $request->selected)->get();
        foreach($trans as $tran)
        {
            $res = $this->notiSender($url, $tran, '');
        }
        return $this->response(1);
    }

    /*
     * 가맹점별 매출집계
     */
    public function mchtGroups(Request $request)
    {
        $cols = [
            'merchandises.id', 'merchandises.mcht_name', 'merchandises.resident_num', 
            'merchandises.business_num', 'merchandises.nick_name', 'merchandises.addr', 
            'merchandises.sector', 'merchandises.custom_id',
        ];
        $cols = array_merge($cols , $this->getTotalCols('mcht_settle_amount'));
        $query = $this->commonSelect($request);        
        $query = $this->transDateFilter($query, $request->s_dt, $request->e_dt, $request->use_search_date_detail);
        $grouped = $query
            ->groupBy('merchandises.id')
            ->orderBy('merchandises.mcht_name')
            ->get($cols);
        return $grouped;
    }
    
    public function notiSend(Request $request, $id)
    {
        $trans = $this->transactions
            ->join('noti_urls', 'transactions.mcht_id', 'noti_urls.mcht_id')
            ->where('transactions.id', $id)
            ->get(['noti_urls.*']);
        foreach($trans as $tran)
        {
            $res = $this->notiSender($tran->url, $tran, '');
        }
        return $this->response(1);
    }

    public function _test()
    {
        $dev_settle_type = DevSettleType::NOT_APPLY->value;
        $db_trans = $this->transactions
            ->where('brand_id', 4)
            ->where('sales4_id', 11551)
            ->orderBy('id', 'desc')
            ->get();

        $trans = json_decode(json_encode($db_trans), true);
        $trans = $this->setSettleAmount($trans, $dev_settle_type);
        $i=0;
        foreach($db_trans as $tran)
        {
            $tran->brand_settle_amount = $trans[$i]['brand_settle_amount'];
            $tran->dev_settle_amount = $trans[$i]['dev_settle_amount'];
            $tran->dev_realtime_settle_amount = $trans[$i]['dev_realtime_settle_amount'];
            $tran->mcht_settle_amount = $trans[$i]['mcht_settle_amount'];
            $tran->sales0_settle_amount = $trans[$i]['sales0_settle_amount'];
            $tran->sales1_settle_amount = $trans[$i]['sales1_settle_amount'];
            $tran->sales2_settle_amount = $trans[$i]['sales2_settle_amount'];
            $tran->sales3_settle_amount = $trans[$i]['sales3_settle_amount'];
            $tran->sales4_settle_amount = $trans[$i]['sales4_settle_amount'];
            $tran->sales5_settle_amount = $trans[$i]['sales5_settle_amount'];
            $tran->save();
            $i++;
            echo $i."\n";
        }
    }

    static public function test()
    {
        $ist = new TransactionController(new Transaction);
        $ist->_test();
    }
}
