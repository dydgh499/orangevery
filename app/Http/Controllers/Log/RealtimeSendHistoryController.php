<?php

namespace App\Http\Controllers\Log;

use App\Models\HeadOfficeAccount;
use App\Models\Log\RealtimeSendHistory;

use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Requests\Manager\IndexRequest;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * @group Realtime-Send-History API
 *
 * 실시간 이체이력 API 입니다.
 */
class RealtimeSendHistoryController extends Controller
{
    use ManagerTrait, ExtendResponseTrait;
    protected $realtime_send_histories, $base_noti_url;

    public function __construct(RealtimeSendHistory $realtime_send_histories)
    {
        $this->realtime_send_histories = $realtime_send_histories;
        $this->base_noti_url = env('NOTI_URL', 'http://localhost:81').'/api/v2/realtimes';
    }
    
    public function commonSelect($request)
    {
        $search = $request->input('search', '');
        $query  = $this->realtime_send_histories
            ->join('transactions', 'realtime_send_histories.trans_id', '=', 'transactions.id')
            ->join('merchandises', 'transactions.mcht_id', '=', 'merchandises.id')
            ->where('transactions.brand_id', $request->user()->brand_id)
            ->where('transactions.is_delete', false)
            ->where('realtime_send_histories.is_delete', false);

        $query = globalPGFilter($query, $request, 'transactions');
        $query = globalSalesFilter($query, $request, 'transactions');
        $query = globalAuthFilter($query, $request, 'transactions');
        
        if($search != '')
        {
            $query = $query->where(function ($query) use ($search) {
                return $query->where('transactions.appr_num', 'like', "%$search%")
                    ->orWhere('merchandises.mcht_name', 'like', "%$search%")
                    ->orWhere('realtime_send_histories.acct_num', 'like', "%$search%");
            });
        }
        return $query;
    }

    /**
     * 목록출력
     *
     */
    public function index(IndexRequest $request)
    {
        $cols = [
            'merchandises.mcht_name',
            'transactions.appr_num',
            'transactions.trx_id',
            'realtime_send_histories.*',
        ];
        $query = $this->commonSelect($request);
        $data = $this->getIndexData($request, $query, 'realtime_send_histories.id', $cols, 'realtime_send_histories.created_at');
        return $this->response(0, $data);
    }

    /**
     * 추가
     *
     * 운영자 이상 가능
     *
     * @bodyParam user_pw string 유저 패스워드
     */
    public function store(Request $request)
    {

    }

    /**
     * 단일조회(상세조회)
     *
     * 운영자 이상 가능
     *
     * @urlParam id integer required 영업자 이력 PK
     */
    public function show($id)
    {
        $data = $this->realtime_send_histories->where('id', $id)->first();
        return $this->response($data ? 0 : 1000, $data);
    }


    public function update(Request $request, $id)
    {
        //
    }

    /**
     * 단일삭제
     *
     * @urlParam id integer required 이상거래 PK
     */
    public function destroy($id)
    {
        //
    }

    /**
     * 잔액조회
     */
    public function getBalance(Request $request)
    {
        $data = $request->all();
        $url = $this->base_noti_url.'/get-balance';
        $res = post($url, $data);
        $res = $res['body']['data'];
        return $this->extendResponse($res['result_cd'] == "0000" ? 1 : 2000, $res['result_msg'], $res['data']);
    }

    /**
     * 본사지정계좌 출금
     */
    public function headOfficeTransfer(Request $request)
    {
        $data = $request->all();
        $privacy = HeadOfficeAccount::where('id', $request->head_office_acct_id)->first();
        $params = [
            'fin_id'    => $request->fin_id,
            'mcht_id'   => -1,
            'withdraw_amount' => $request->withdraw_amount,
            'withdraw_fee' => 0,
        ];
        $params = array_merge($params, $privacy->toArray());
        $url = $this->base_noti_url.'/single-deposit';
        $res = post($url, $params);
        if($res['code'] == 201)
            return $this->extendResponse(1, $res['body']['result_msg']);
        else
            return $this->extendResponse(2000, $res['body']['result_msg']);
    }
}
