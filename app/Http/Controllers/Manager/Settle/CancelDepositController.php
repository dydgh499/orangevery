<?php

namespace App\Http\Controllers\Manager\Settle;

use App\Enums\HistoryType;

use App\Http\Controllers\Ablilty\ActivityHistoryInterface;

use App\Models\Transaction;
use App\Models\CancelDeposit;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Controllers\Manager\Transaction\SettleDateCalculator;
use App\Http\Controllers\Manager\Transaction\TransactionAPI;

use App\Http\Requests\Manager\Settle\CancelDepositRequest;
use App\Http\Requests\Manager\IndexRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * @group Cancel deposit API
 *
 * 취소 입금 API 입니다.
 */
class CancelDepositController extends Controller
{
    use ManagerTrait, ExtendResponseTrait;
    protected $deposits;
    protected $target;

    public function __construct(CancelDeposit $deposits)
    {
        $this->deposits = $deposits;
        $this->target = '수기입금';
    }

    /**
     * 목록출력
     *
     * 가맹점 이상 가능
     *
     * @queryParam search string 검색어(가맹점 상호)
     */
    public function index(IndexRequest $request)
    {
        $search = $request->input('search', '');
        $cols = [
            'cancel_deposits.*',
            'transactions.acquirer',
            'transactions.installment',
            'transactions.amount',
            'transactions.mcht_settle_amount as profit',
            'transactions.appr_num',
            'merchandises.mcht_name',
            DB::raw("concat(trx_dt, ' ', trx_tm) AS trx_dttm"),
            DB::raw("concat(cxl_dt, ' ', cxl_tm) AS cxl_dttm"),
        ];
        $query = $this->deposits->join('transactions', 'transactions.id', '=', 'cancel_deposits.trans_id')
                ->join('merchandises', 'merchandises.id', '=', 'transactions.mcht_id')
                ->where('transactions.brand_id', $request->user()->brand_id)
                ->where('merchandises.mcht_name', 'like', "%$search%");
        $query = globalSalesFilter($query, $request, 'merchandises');
        $query = globalAuthFilter($query, $request, 'merchandises');
        return $this->getIndexData($request, $query, 'transactions.id', $cols, 'transactions.trx_at');
    }

    public function getSettleDateByTransId($trans_id, $deposit_dt)
    {
        $tran = Transaction::where('id', $trans_id)->first();
        return SettleDateCalculator::getSettleDate(request()->user()->brand_id, $deposit_dt, $tran->mcht_settle_type, $tran->pg_settle_type);
    }

    /**
     * 추가
     *
     * 본사 이상 가능
     */
    public function store(CancelDepositRequest $request)
    {
        $data = $request->data();
        $data['settle_dt'] = $this->getSettleDateByTransId($data['trans_id'], $data['deposit_dt']);
        $cd_res = app(ActivityHistoryInterface::class)->add($this->target, $this->deposits, $data, 'trans_id');
        if($cd_res)
        {
            $cancel_deposit_id = $cd_res->id;
            if($request->va_id)
            {
                $res = TransactionAPI::createCancelDeposit([
                    'id'    => $cancel_deposit_id,
                    'va_id' => $request->va_id,
                ]);
                if($res['body']['result_cd'] === '0000')
                    return $this->response(1, ['id' => $cancel_deposit_id]);
                else
                    return $this->apiResponse($res['body']['result_cd'], $res['body']['result_msg'], $res['body']);
            }
            else
                return $this->response(1, ['id' => $cancel_deposit_id]);
        }
        else
            return $this->response(990);
    }

    /**
     * 단일조회
     *
     * 가맹점 이상 가능
     *
     * @urlParam id integer required 정기등록카드 PK
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $cols = [
            'cancel_deposits.deposit_amount',
            'cancel_deposits.deposit_history',
            'transactions.amount',
            'transactions.mcht_settle_amount',
            DB::raw("concat(trx_dt, ' ', trx_tm) AS trx_dttm"),
            DB::raw("concat(cxl_dt, ' ', cxl_tm) AS cxl_dttm"),
        ];
        $data = $this->deposits
                ->join('transactions', 'transactions.trx_id', '=', 'cancel_deposits.trans_id')
                ->first($cols);
        return $this->response($data ? 0 : 1000, $data);
    }

    /**
     * 업데이트
     *
     * 본사 이상 가능
     *
     * @urlParam id integer required 정기등록카드 PK
     * @return \Illuminate\Http\Response
     */
    public function update(CancelDepositRequest $request, int $id)
    {
        if($request->va_id)
            return $this->extendResponse(991, '정산지갑 사용 매출은 업데이트 할 수 없습니다.');
        else
        {
            $query = $this->deposits->where('id', $id);
            $data = $request->data();
            $data['settle_dt'] = $this->getSettleDateByTransId($data['trans_id'], $data['deposit_dt']);
            $row = app(ActivityHistoryInterface::class)->update($this->target, $query, $data, 'trans_id');

            return $this->response($res ? 1 : 990, ['id'=>$id]);    
        }
    }

    /**
     * 단일삭제
     *
     * 본사 이상 가능
     *
     * @urlParam id integer required 정기등록카드 PK
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if($request->va_id)
            return $this->extendResponse(991, '정산지갑 사용 매출은 삭제 할 수 없습니다.');
        else
        {
            $query  = $this->deposits->where('id', $id);
            $row    = app(ActivityHistoryInterface::class)->destory($this->target, $query, 'trans_id', '', HistoryType::DELETE, false);
            return $this->response(1, ['id' => $id]);    
        }
    }
}
