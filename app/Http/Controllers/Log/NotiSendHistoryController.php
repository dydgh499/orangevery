<?php

namespace App\Http\Controllers\Log;

use App\Models\Log\NotiSendHistory;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Traits\Settle\TransactionTrait;
use App\Http\Requests\Manager\IndexRequest;

/**
 * @group Noti-Send-History API
 *
 * 노티 발송이력 API 입니다.
 */
class NotiSendHistoryController extends Controller
{
    use ManagerTrait, ExtendResponseTrait, TransactionTrait;
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
        ];
        $query  = $this->noti_send_histories
            ->join('transactions', 'noti_send_histories.trans_id', '=', 'transactions.id')            
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

        $data = $this->getIndexData($request, $query, 'noti_send_histories.id', $cols, 'noti_send_histories.created_at');
        return $this->response(0, $data);
    }
    
    private function save($res, $noti)
    {
        $body = json_encode($res['body']);
        $log = [
            'http_code' => $res['code'],
            'message'   => $body ? $body : $res['body'],
            'send_url'  => $noti->send_url,
            'trans_id'  => $noti->id,
            'brand_id'  => $noti->brand_id,
            'retry_count' => $noti->retry_count+1,
        ];
        return $this->noti_send_histories->create($log);        
    }

    /*
     * 노티 재전송
     */
    public function retry(Request $request, $trans_id)
    {
        $noti  = $this->noti_send_histories
            ->join('transactions', 'noti_send_histories.trans_id', '=', 'transactions.id')
            ->where('noti_send_histories.trans_id', $trans_id)
            ->orderby('retry_count', 'desc')
            ->first($this->cols);

        $res = $this->notiSender($noti->send_url, $noti, $noti->temp);
        $res = $this->save($res, $noti);
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
            $res = $this->notiSender($noti->send_url, $noti, $noti->temp);
            $res = $this->save($res, $noti);
        }
        return $this->response(1);
    }
    
    /*
     * 노티 상세정보 확인
     */
    public function show(Request $request, $trans_id)
    {
        $noti  = $this->noti_send_histories
            ->join('transactions', 'noti_send_histories.trans_id', '=', 'transactions.id')
            ->where('noti_send_histories.trans_id', $trans_id)
            ->first($this->cols);
        $params = $this->getNotiSendFormat($noti, $noti->temp);
        $params['send_url'] = $noti->send_url;
        return $this->response(0, $params);
    }
}
