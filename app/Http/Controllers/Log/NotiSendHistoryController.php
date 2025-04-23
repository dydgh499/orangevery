<?php

namespace App\Http\Controllers\Log;

use App\Models\Log\NotiSendHistory;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Manager\Transaction\NotiRetrySender;
use App\Http\Controllers\Manager\Transaction\TransactionFilter;

use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Requests\Manager\IndexRequest;
use App\Http\Controllers\Utils\Comm;

use Illuminate\Support\Facades\DB;
/**
 * @group Noti-Send-History API
 *
 * 노티 발송이력 API 입니다.
 */
class NotiSendHistoryController extends Controller
{
    use ManagerTrait, ExtendResponseTrait;
    protected $noti_send_histories;

    public function __construct(NotiSendHistory $noti_send_histories)
    {
        $this->noti_send_histories = $noti_send_histories;
        $this->cols = ['transactions.*', 'noti_send_histories.send_url', 'noti_send_histories.retry_count', 'noti_send_histories.temp'];
    }

    public function index(IndexRequest $request)
    {
        $search = $request->input('search', '');
        $cols = [
            'noti_send_histories.*', 
            'transactions.appr_num', 
            'transactions.mid', 
            'transactions.tid',
            'transactions.is_cancel',
            'transactions.module_type',
            
            'transactions.trx_dt', 'transactions.trx_tm', 
            'transactions.cxl_dt', 'transactions.cxl_tm',
            DB::raw("concat(trx_dt, ' ', trx_tm) AS trx_dttm"), 
            DB::raw("concat(cxl_dt, ' ', cxl_tm) AS cxl_dttm"),

            'transactions.appr_num',
            'transactions.amount',
            'transactions.installment',
            'transactions.acquirer',
            'transactions.card_num',
            
            'transactions.issuer',
            'transactions.buyer_name',
            'transactions.buyer_phone',
            'transactions.item_name',
            'transactions.trx_id',
            'transactions.ori_trx_id',

            'merchandises.mcht_name',
            'payment_modules.note as pmod_note',
        ];
        $query  = $this->noti_send_histories
            ->join('transactions', 'noti_send_histories.trans_id', '=', 'transactions.id')
            ->join('merchandises', 'transactions.mcht_id', '=', 'merchandises.id')
            ->join('payment_modules', 'transactions.pmod_id', '=', 'payment_modules.id')
            ->where('noti_send_histories.brand_id', $request->user()->brand_id)
            ->where('noti_send_histories.is_delete', false);

        $query = globalPGFilter($query, $request, 'transactions');
        $query = globalSalesFilter($query, $request, 'transactions');
        $query = globalAuthFilter($query, $request, 'transactions');
        
        $query = $query->where(function($query) use ($search) {
            return $query->where('merchandises.mcht_name', 'like', "%$search%")
                ->orWhere('transactions.appr_num', 'like', "%$search%")
                ->orWhere('transactions.mid', 'like', "%$search%")
                ->orWhere('transactions.tid', 'like', "%$search%");
        });

        if((int)$request->result_status === 1) {
            $query = $query->where(function($query) {
                return $query->where('noti_send_histories.http_code', 200)
                    ->orWhere('noti_send_histories.http_code', 201);
            });
        }
        else if((int)$request->result_status === 2) {
            $query = $query->where(function($query) {
                return $query->where('noti_send_histories.http_code', '!=', 200)
                    ->where('noti_send_histories.http_code', '!=', 201);
            });
        }
        $data = $this->getIndexData($request, $query, 'noti_send_histories.id', $cols, 'noti_send_histories.created_at');
        return $this->response(0, $data);
    }

    /*
     * 노티 재전송
     */
    public function retry(Request $request)
    {
        $validated = $request->validate(['trx_ids.*'=>'required|integer']);
        $url = env('NOTI_URL', 'http://localhost:81').'/api/v2/noti-retry';
        $res = Comm::post($url, [
            'trx_ids' => $request->trx_ids,
        ]);
        return $this->apiResponse($res['body']['result_cd'], $res['body']['result_msg']);
    }

    /*
     * 노티 자체 재전송
     */
    public function selfRetry(Request $request) 
    {
        $validated = $request->validate(['trx_ids.*'=>'required|integer']);
        $url = env('NOTI_URL', 'http://localhost:81').'/api/v2/noti-self-retry';
        $res = Comm::post($url, [
            'trx_ids' => $request->trx_ids,
        ]);
        return $this->apiResponse($res['body']['result_cd'], $res['body']['result_msg']);
    }
    
    /*
     * 노티 상세정보 확인
     */
    public function show(Request $request, int $id)
    {
        $noti  = $this->noti_send_histories
            ->join('transactions', 'noti_send_histories.trans_id', '=', 'transactions.id')
            ->where('noti_send_histories.id', $id)
            ->first($this->cols);
        [$params, $headers] = NotiRetrySender::getNotiSendFormat($noti, $noti->temp);
        $params['send_url'] = $noti->send_url;
        return $this->response(0, $params);
    }

    /**
     * 단일삭제
     *
     * @urlParam id integer required 노티 PK
     */
    public function destory(Request $request, int $id)
    {
        $res = $this->delete($this->noti_send_histories->where('id', $id));
        return $this->response($res ? 1 : 990, ['id'=>$id]);
    }
}
