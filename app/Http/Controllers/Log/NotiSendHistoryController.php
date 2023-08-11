<?php

namespace App\Http\Controllers\Log;

use App\Models\Log\NotiSendHistory;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Requests\Manager\IndexRequest;

class NotiSendHistoryController extends Controller
{
    use ManagerTrait, ExtendResponseTrait;
    protected $noti_send_histories;

    public function __construct(NotiSendHistory $noti_send_histories)
    {
        $this->noti_send_histories = $noti_send_histories;
        $this->cols = ['transactions.*', 'noti_send_histories.send_url', 'noti_send_histories.retry_count'];
    }

    public function index(IndexRequest $request)
    {
        $search = $request->input('search', '');
        $cols = [
            'noti_send_histories.*', 
            'transactions.appr_num', 
            'transactions.mid', 
            'transactions.tid'
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

    private function getNotiSendFormat($noti)
    {
        $params = [
            'mid'   => $noti->mid,
            'tid'    => $noti->tid,
            'trx_id'    => $noti->trx_id,
            'amount'    => $noti->amount,
            'ord_num'   => $noti->ord_num,
            'appr_num'  => $noti->appr_num,
            'item_name' => $noti->item_name,
            'buyer_name'    => $noti->buyer_name,
            'buyer_phone'   => $noti->buyer_phone,
            'acquirer'      => $noti->acquirer,
            'issuer'        => $noti->issuer,
            'card_num'      => $noti->card_num,
            'installment'   => $noti->installment,
            'pay_dttm'      => $noti->trx_dt." ".$noti->trx_tm,
            'is_cancel'     => $noti->is_cancel,
            'temp'          => $noti->temp,
        ];
        if($noti->is_cancel)
            $params['cxl_dttm'] = $noti->cxl_dttm;
        return $params;
    }

    public function retry(Request $request, $trans_id)
    {
        $noti  = $this->noti_send_histories
            ->join('transactions', 'noti_send_histories.trans_id', '=', 'transactions.id')
            ->where('noti_send_histories.trans_id', $trans_id)
            ->orderby('created_at', 'desc')
            ->first($this->cols);

        $params = $this->getNotiSendFormat($noti);
        $headers = [
            'Content-Type'  => 'application/json',
            'Accept' => 'application/json',
        ];
        $res  = post($noti->send_url, $params, $headers);
        $log = [
            'http_code' => $res['code'],
            'message'   => json_encode($res['body']),
            'send_url'  => $noti->send_url,
            'trans_id'  => $trans_id,
            'brand_id'  => $noti->brand_id,
            'retry_count' => $noti->retry_count+1,
        ];
        $res = $this->noti_send_histories->create($log);
        return $this->response($res ? 1 : 990);
    }
    
    public function detail(Request $request, $trans_id)
    {
        $noti  = $this->noti_send_histories
            ->join('transactions', 'noti_send_histories.trans_id', '=', 'transactions.id')
            ->where('noti_send_histories.trans_id', $trans_id)
            ->first($this->cols);
        $params = $this->getNotiSendFormat($noti);
        $params['send_url'] = $noti->send_url;
        return $this->response(0, $params);
    }
}
