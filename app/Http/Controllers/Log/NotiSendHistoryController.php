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
            return $query->where('transactions.appr_num', 'like', "%$search%")
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
    public function retry(Request $request, int $id)
    {
        $noti  = $this->noti_send_histories
            ->join('transactions', 'noti_send_histories.trans_id', '=', 'transactions.id')
            ->where('noti_send_histories.id', $id)
            ->orderby('retry_count', 'desc')
            ->first($this->cols);

        $res = NotiRetrySender::notiSender($noti->send_url, $noti, $noti->temp);
        $res = NotiRetrySender::save($res, $noti);
        return $this->response($res ? 1 : 990);
    }

    /*
     * 노티 대량 재전송
     */
    public function batchRetry(Request $request) 
    {
        $notis = $this->noti_send_histories
            ->join('transactions', 'noti_send_histories.trans_id', '=', 'transactions.id')
            ->whereIn('noti_send_histories.id', $request->selected)
            ->get($this->cols);

        foreach($notis as $noti)
        {
            $res = NotiRetrySender::notiSender($noti->send_url, $noti, $noti->temp);
            $res = NotiRetrySender::save($res, $noti);
        }
        return $this->response(1);
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
