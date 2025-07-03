<?php

namespace App\Http\Controllers\Manager\Pay;

use App\Models\Transaction;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;

use App\Models\Pay\PaymentModule;
use App\Http\Requests\Manager\TransactionRequest;
use App\Http\Requests\Manager\IndexRequest;
use App\Http\Requests\Manager\Transaction\HandPayRequest;
use App\Http\Requests\Manager\Transaction\PayCancelRequest;

use App\Http\Controllers\Manager\Transaction\TransactionFilter;
use App\Http\Controllers\Manager\Transaction\TransactionAPI;

use App\Http\Controllers\Utils\ChartFormat;

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
    
    public function __construct(Transaction $transactions)
    {
        $this->transactions = $transactions;
        $this->target = '정산현황';
    }

    /**
     * 차트 데이터 출력
     *
     * 가맹점 이상 가능
     */
    public function chart(IndexRequest $request)
    {
        $query = TransactionFilter::common($request);
        $chart = $query->first([
            DB::raw("SUM(IF(is_cancel = 0, amount, 0)) AS appr_amount"),
            DB::raw("SUM(is_cancel = 0) AS appr_count"),
            DB::raw("SUM(IF(is_cancel = 1, amount, 0)) AS cxl_amount"),
            DB::raw("SUM(is_cancel = 1) AS cxl_count"),
        ]);
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
        $cols = [
            'transactions.*',
            'payment_modules.note',
        ];
        $query = TransactionFilter::common($request);
        $data = TransactionFilter::pagenation($request, $query, $cols, 'transactions.trx_at', false);
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
        return $this->extendResponse(951, '사용중지기능');
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
        return $this->extendResponse(951, '사용중지기능');
    }

    /**
     * 단일삭제
     *
     * @urlParam id integer required 유저 PK
     */
    public function destroy(Request $request, int $id)
    {
        return $this->extendResponse(951, '사용중지기능');
    }

    /**
     * 수기결제
     *
     */
    public function handPay(HandPayRequest $request)
    {
        $pay_module = PaymentModule::where('id', $request->pmod_id)->first();
        $res = TransactionAPI::handPay($request->all(), $pay_module->api_key);
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
        $pay_module = PaymentModule::where('id', $request->pmod_id)->first();
        $ord_trans = $this->transactions->where('trx_id', $request->trx_id)->first();
        if (!$pay_module) 
            return $this->apiResponse('9999', '결제모듈이 존재하지 않습니다.');
        if (!$ord_trans)
            return $this->apiResponse('9999', '원거래가 존재하지 않습니다.');
        else
        {
            $res = TransactionAPI::payCancel($request->all(), $pay_module->api_key);
            if($res['body']['result_cd'] === '0000')
            {
                $cxl_seq = $this->transactions->where('ori_trx_id', $res['body']['trx_id'])->count() + 1;
                $cxl_trans = $ord_trans->replicate();
                $cxl_trans->trx_id      = $res['body']['trx_id'];
                $cxl_trans->ori_trx_id  = $res['body']['ori_trx_id'];
                $cxl_trans->trx_at      = $res['body']['cxl_dttm'];
                $cxl_trans->is_cancel   =  1;
                $cxl_trans->cxl_seq     =  $cxl_seq;
                $cxl_trans->amount   =  $cxl_trans->amount * -1;
                $cxl_trans->save();
                return $this->response(1, $res['body']);
            }
            else
                return $this->apiResponse($res['body']['result_cd'], $res['body']['result_msg'], $res['body']);
        }
    }
}
