<?php

namespace App\Http\Controllers\Manager;

use App\Models\CancelDeposit;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Traits\Settle\TransactionTrait;

use App\Http\Requests\Manager\BulkRegister\BulkRegularCardRequest;
use App\Http\Requests\Manager\CancelDepositRequest;
use App\Http\Requests\Manager\IndexRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * @group Cancel deposit API
 *
 * 취소 입금 API 입니다.
 */
class CancelDepositController extends Controller
{
    use ManagerTrait, ExtendResponseTrait, TransactionTrait;
    protected $deposits;

    public function __construct(CancelDeposit $deposits)
    {
        $this->deposits = $deposits;
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
            'cancel_deposits.id',
            'cancel_deposits.deposit_amount',
            'cancel_deposits.deposit_history',
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
        $query = $this->transDateFilter($query, $request->s_dt, $request->e_dt, $request->use_search_date_detail);
        return $this->transPagenation($query, 'transactions', $cols, $request->page, $request->page_size);
    }

    /**
     * 추가
     *
     * 마스터 이상 가능
     *
     */
    public function store(CancelDepositRequest $request)
    {
        $data = $request->data();
        $res = $this->deposits->create($data);
        return $this->response($res ? 1 : 990, ['id'=>$res->id]);
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
     * 마스터 이상 가능
     *
     * @urlParam id integer required 정기등록카드 PK
     * @return \Illuminate\Http\Response
     */
    public function update(CancelDepositRequest $request, $id)
    {
        $data = $request->data();
        $res  = $this->deposits->where('id', $id)->update($data);
        return $this->response($res ? 1 : 990, ['id'=>$id]);
    }

    /**
     * 단일삭제
     *
     * 마스터 이상 가능
     *
     * @urlParam id integer required 정기등록카드 PK
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $res = $this->deposits->where('id', $id)->delete();
        return $this->response($res ? 1 : 990, ['id'=>$id]);
    }
}
