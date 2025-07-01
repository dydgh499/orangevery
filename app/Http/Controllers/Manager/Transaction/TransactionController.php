<?php

namespace App\Http\Controllers\Manager\Transaction;

use App\Enums\HistoryType;
use App\Http\Controllers\Ablilty\Ablilty;
use App\Http\Controllers\Ablilty\EditAbleWorkTime;
use App\Http\Controllers\Ablilty\ActivityHistoryInterface;

use App\Models\Transaction;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;

use App\Http\Requests\Manager\TransactionRequest;
use App\Http\Requests\Manager\IndexRequest;
use App\Http\Requests\Manager\Transaction\HandPayRequest;
use App\Http\Requests\Manager\Transaction\PayCancelRequest;

use App\Http\Controllers\Manager\Salesforce\UnderSalesforce;
use App\Http\Controllers\Manager\Transaction\TransactionFilter;
use App\Http\Controllers\Manager\Transaction\TransactionAPI;

use App\Http\Controllers\Utils\ChartFormat;

use App\Http\Controllers\Manager\Service\BrandInfo;
use Illuminate\Database\QueryException;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            'transactions.*',
            'payment_modules.note', 'payment_modules.use_realtime_deposit', 
            'payment_modules.cxl_type', 'payment_modules.va_id',
            DB::raw("concat(trx_dt, ' ', trx_tm) AS trx_dttm"),
            DB::raw("concat(cxl_dt, ' ', cxl_tm) AS cxl_dttm"),
        ];
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

        $with  = ['cancelDeposits'];
        $query = TransactionFilter::common($request);

        if($b_info['pv_options']['paid']['use_noti'])
            $with[] = 'notiSendHistories';
        if($b_info['pv_options']['paid']['use_realtime_deposit'])
            $with[] = 'withdrawHistories';

        if(count($with))
            $query = $query->with($with);
        $data = TransactionFilter::pagenation($request, $query, $this->cols, 'transactions.trx_at', false);
        $data = TransactionAPI::getNotiStatus($b_info, $data);        

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
        return $this->extendResponse(951, '사용중지기능');
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
            $query = $this->transactions->where('id', $id);
            $tran = $query->first();
            $data = $request->data();

            $row = app(ActivityHistoryInterface::class)->update($this->target, $query, $data, 'trx_id');
            if($row)
                return $this->response(1, ['id' => $id]);
            else
                return $this->response(990);
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
        if(Ablilty::isOperator($request))
        {
            if(EditAbleWorkTime::validate() === false)
                return $this->extendResponse(1500, '지금은 작업할 수 없습니다.');
            else
            {
                $query  = $this->transactions->where('id', $id);
                $row    = app(ActivityHistoryInterface::class)->destory($this->target, $query, 'trx_id', '', HistoryType::DELETE, false);
                return $this->response(1, ['id' => $id]);    
            }
        }
        else
            return $this->response(951);
    }

    /**
     * 취소매출 생성
     *
     */
    public function cancel(Request $request)
    {
        [$code, $message, $data] = TransactionAPI::createCancel($request);
        if($code)
            return $this->response(1);
        else
            return $this->extendResponse(991, $message);
    }

    /**
     * 수기결제
     *
     */
    public function handPay(HandPayRequest $request)
    {
        $res = TransactionAPI::handPay($request->all());
        if($res['body']['result_cd'] === '0000')
            return $this->response(1, $res['body']);
        else
            return $this->apiResponse($res['body']['result_cd'], $res['body']['result_msg'], $res['body']);
    }

    /**
     * 결제취소
     *
     */
    public function payCancel(PayCancelRequest $request)
    {
        $res = TransactionAPI::payCancel($request->all());
        if($res['body']['result_cd'] === '0000')
            return $this->response(1, $res['body']);
        else
            return $this->apiResponse($res['body']['result_cd'], $res['body']['result_msg'], $res['body']);
    }
}
