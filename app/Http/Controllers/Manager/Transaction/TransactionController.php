<?php

namespace App\Http\Controllers\Manager\Transaction;

use App\Models\Transaction;
use App\Models\Log\RealtimeSendHistory;
use App\Models\Salesforce;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;

use App\Http\Requests\Manager\TransactionRequest;
use App\Http\Requests\Manager\IndexRequest;
use App\Http\Controllers\QuickView\QuickViewController;
use App\Http\Controllers\Manager\Salesforce\UnderSalesforce;
use App\Http\Controllers\Manager\Transaction\NotiRetrySender;
use App\Http\Controllers\Manager\Transaction\SettleDateCalculator;
use App\Http\Controllers\Manager\Transaction\SettleAmountCalculator;
use App\Http\Controllers\Manager\Transaction\TransactionFilter;

use App\Http\Controllers\Utils\Comm;
use App\Http\Controllers\Utils\ChartFormat;

use App\Http\Controllers\Manager\Service\BrandInfo;
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
    use ManagerTrait, ExtendResponseTrait;
    protected $transactions;
    protected $target;
    public $cols;
    
    public function __construct(Transaction $transactions)
    {
        $this->transactions = $transactions;
        $this->target = '매출';
        $this->cols = [
            'merchandises.mcht_name', 'merchandises.user_name', 'merchandises.nick_name', 'merchandises.contact_num',
            'merchandises.addr', 'merchandises.resident_num', 'merchandises.business_num', 'merchandises.tax_category_type',
            'merchandises.use_saleslip_prov', 'merchandises.use_saleslip_sell', 'merchandises.use_collect_withdraw', 'merchandises.is_show_fee',
            'transactions.*',
            'payment_modules.note', 'payment_modules.use_realtime_deposit', 'payment_modules.cxl_type', 'payment_modules.fin_trx_delay',
            DB::raw("concat(trx_dt, ' ', trx_tm) AS trx_dttm"),
            DB::raw("concat(cxl_dt, ' ', cxl_tm) AS cxl_dttm"),
        ];
    }

    public function setTransactionData($level)
    {
        [$target_id, $target_settle_id, $target_settle_amount] = getTargetInfo($level);
        if($target_settle_amount === 'dev_settle_amount')
            $profit = DB::raw("($target_settle_amount + dev_realtime_settle_amount) AS profit");
        else
            $profit = "$target_settle_amount AS profit";
        $this->cols[] = $profit;
    }

    /**
     * 차트 데이터 출력
     *
     * 가맹점 이상 가능
     */
    public function chart(IndexRequest $request)
    {
        [$target_id, $target_settle_id, $target_settle_amount] = getTargetInfo($request->level);

        $query = TransactionFilter::common($request);
        $cols = TransactionFilter::getTotalCols($target_settle_amount);
        $chart = $query->first($cols);
        $chart = ChartFormat::transaction($chart);
        return $this->response(0, $chart);
    }

    /**
     * 목록출력
     *
     * 가맹점 이상 가능
     */
    public function index(IndexRequest $request)
    {
        $b_info = BrandInfo::getBrandById($request->user()->brand_id);
        $this->setTransactionData($request->level);

        $with  = ['cancelDeposits'];
        $query = TransactionFilter::common($request);
        if($request->use_realtime_deposit && (int)$request->level === 10)
            $with[] = 'realtimes';
        if($b_info['pv_options']['paid']['use_noti'])
            $with[] = 'notiSendHistories';
            
        if(count($with))
            $query = $query->with($with);
        $data = TransactionFilter::pagenation($request, $query, $this->cols, 'transactions.trx_at', false);
        $sales_ids      = globalGetUniqueIdsBySalesIds($data['content']);
        $salesforces    = globalGetSalesByIds($sales_ids);
        $data['content'] = globalMappingSales($salesforces, $data['content']);
        
        foreach($data['content'] as $content)
        {
            $content = UnderSalesforce::setViewableSalesInfos($request, $content);
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
            $data['dev_fee'] = (((float)$request->input('dev_fee', 0))/100);
            $data['dev_realtime_fee'] = (((float)$request->input('dev_realtime_fee', 0))/100);

            $data['settle_dt'] = SettleDateCalculator::getSettleDate($request->user()->brand_id, $data['is_cancel'] ? $data['cxl_dt'] : $data['trx_dt'], $data['mcht_settle_type'], 1);
            $data['pg_settle_type'] = 1;
            if($data['dev_fee'] >= 1)
                return $this->extendResponse(991, '개발사 수수료가 이상합니다.<br>관리자에게 문의하세요.');
            else
            {
                [$data] = SettleAmountCalculator::setSettleAmount([$data]);
                $res = $this->transactions->create($data);
                operLogging(HistoryType::CREATE, $this->target, [], $data, "#".$res->id);
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
    public function show(Request $request, int $id)
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
    public function update(TransactionRequest $request, int $id)
    {
        try
        {
            $tran = $this->transactions->where('id', $id)->first();
            $data = $request->data();

            $data['settle_dt'] = SettleDateCalculator::getSettleDate($data['brand_id'], ($data['is_cancel'] ? $data['cxl_dt'] : $data['trx_dt']), $data['mcht_settle_type'], 1);
            $data['pg_settle_type'] = 1;

            $data['dev_fee'] = $tran->dev_fee;
            $data['dev_realtime_fee'] = $tran->dev_realtime_fee;

            [$data] = SettleAmountCalculator::setSettleAmount([$data]);
            $res = $this->transactions->where('id', $id)->update($data);
            operLogging(HistoryType::UPDATE, $this->target, $tran, $data, "#".$id);
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
    public function destroy(Request $request, int $id)
    {
        if($this->authCheck($request->user(), $id, 35))
        {
            $res = $this->transactions->where('id', $id)->delete();
            operLogging(HistoryType::DELETE, $this->target, ['id' => $id], ['id' => $id], "#".$id);
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
        $data['hold_fee'] = $request->input('hold_fee', 0);
        $data['mcht_fee'] = $request->input('mcht_fee', 0);
        $data['sales0_fee'] = $request->input('sales0_fee', 0);
        $data['sales1_fee'] = $request->input('sales1_fee', 0);
        $data['sales2_fee'] = $request->input('sales2_fee', 0);
        $data['sales3_fee'] = $request->input('sales3_fee', 0);
        $data['sales4_fee'] = $request->input('sales4_fee', 0);
        $data['sales5_fee'] = $request->input('sales5_fee', 0);

        $data['settle_dt'] = SettleDateCalculator::getSettleDate($data['brand_id'], $data['cxl_dt'], $data['mcht_settle_type'], $request->pg_settle_type);
        try 
        {
            [$data] = SettleAmountCalculator::setSettleAmount([$data]);
            $res = $this->transactions->create($data);
            operLogging(HistoryType::CREATE, $this->target, [], $data, "#".$res->id);
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
        $res = Comm::post($url, $data);
        
        if($res['body']['result_cd'] === '0000')
            return $this->response(1, $res['body']);
        else
            return $this->apiResponse($res['body']['result_cd'], $res['body']['result_msg'], $res['body']);
    }

    /**
     * 결제취소
     *
     */
    public function payCancel(Request $request)
    {
        $data = $request->all();
        $res = Comm::post(env('NOTI_URL', 'http://localhost:81').'/api/v2/online/pay/cancel', $data);
        if($res['body']['result_cd'] === '0000')
            return $this->response(1, $res['body']);
        else
            return $this->apiResponse($res['body']['result_cd'], $res['body']['result_msg'], $res['body']);

    }

    /*
     * 노티 자체 대량 재전송
     */
    public function batchSelfRetry(Request $request)
    {
        $url = env('NOTI_URL', 'http://localhost:81').'/api/v2/noti/payvery';
        $trans = $this->transactions->whereIn('id', $request->selected)->get();
        foreach($trans as $tran)
        {
            $res = NotiRetrySender::notiSender($url, $tran, '');
        }
        return $this->response(1);
    }

    /*
     * 노티 대량 재전송
     */
    public function batchRetry(Request $request)
    {
        $fail_res    = [];
        $success_res = [];
        for ($i=0; $i <count($request->selected); $i++) 
        {
            [$_success_res, $_fail_res] = NotiRetrySender::notiSenderWrap($request->selected[$i]);
            $success_res = array_merge($success_res, $_success_res);
            $fail_res = array_merge($fail_res, $_fail_res);
        }
        if(count($fail_res))
        {
            $message = "일괄작업에 실패한 노티건들이 존재합니다.<br><br>".implode(' ', $fail_res);
            return $this->extendResponse(2000, $message);
        }
        else
            return $this->response(1, $success_res);
    }

    /*
    * 노티 전송 -> 가맹점
    */
    public function noti(Request $request, int $id)
    {
        [$success_res, $fail_res]  = NotiRetrySender::notiSenderWrap($id);        
        if(count($fail_res))
        {
            $message = "일괄작업에 실패한 노티건들이 존재합니다.<br><br>".implode(' ', $fail_res);
            return $this->extendResponse(2000, $message);
        }
        else
            return $this->response(1, $success_res);
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
        $cols = array_merge($cols , TransactionFilter::getTotalCols('mcht_settle_amount'));

        $query = TransactionFilter::common($request);
        $grouped = $query
                ->groupBy('merchandises.id')
                ->orderBy('merchandises.mcht_name')
                ->get($cols);
        return $grouped;
    }

    public function _test()
    {
        $db_trans = $this->transactions
            ->where('trx_at', '>=', '2024-12-19 00:00:00')
            ->orderBy('id', 'desc')
            ->get();

        $i=0;
        foreach($db_trans as $tran)
        {
            $tran->settle_dt = SettleDateCalculator::getSettleDate($tran->brand_id, $tran->is_cancel ? $tran->cxl_dt : $tran->trx_dt, $tran->mcht_settle_type, $tran->pg_settle_type);
            $tran->save();
            $i++;
            echo $i."\n";
        }
    }

    static public function test()
    {
        $inst = new TransactionController(new Transaction);
        $inst->_test();
    }
}
