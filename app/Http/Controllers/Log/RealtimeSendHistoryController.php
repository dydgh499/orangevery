<?php

namespace App\Http\Controllers\Log;

use App\Models\Log\RealtimeSendHistory;

use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Requests\Manager\IndexRequest;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RealtimeSendHistoryController extends Controller
{
    use ManagerTrait, ExtendResponseTrait;
    protected $realtime_send_histories, $base_noti_url;

    public function __construct(RealtimeSendHistory $realtime_send_histories)
    {
        $this->realtime_send_histories = $realtime_send_histories;
        $this->base_noti_url = $url = env('NOTI_URL', 'http://localhost:81').'/api/v2/realtimes';
    }
       
    /**
     * 목록출력
     *
     */
    public function index(IndexRequest $request)
    {
        $search = $request->input('search', '');
        $cols = [
            'merchandises.mcht_name',
            'transactions.appr_num',
            'transactions.trx_id',
            'realtime_send_histories.*',
        ];
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
        $data = $this->getIndexData($request, $query, 'realtime_send_histories.id', $cols, 'realtime_send_histories.created_at');
        return $this->response(0, $data);
    }

    /**
     * 추가
     *
     * 운영자 이상 가능
     *
     * @bodyParam user_pw string 유저 패스워드
     * @return \Illuminate\Http\JsonResponse
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
     * @return \Illuminate\Http\JsonResponse
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        //
    }

    public function getBalance(Request $request)
    {
        $data = $request->all();
        $url = $this->base_noti_url.'/get-balance';
        $res = post($url, $data);
        return $this->response(1, $res['body']);
    }

    public function deposit(Request $request)
    {
        $validated = $request->validate(['trans_id'=>'required|integer', 'mcht_id'=>'required|integer']);
        $data = $request->all();
        $url = $this->base_noti_url.'/deposit';
        $res = post($url, $data);
        return $this->response(1, $res['body']);
    }

    public function depositCollect(Request $request)
    {
        
    }
}
