<?php

namespace App\Http\Controllers\Manager\Transaction;

use App\Models\Log\NotiSendHistory;
use App\Models\Transaction;
use Carbon\Carbon;

class NotiRetrySender
{
    static public function save($res, $noti)
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
        return NotiSendHistory::create($log);        
    }

    static public function getNotiSendFormat($tran, $temp='')
    {
        $params = [
            'mid'   => $tran->mid,
            'tid'    => $tran->tid,
            'trx_id'    => $tran->trx_id,
            'amount'    => $tran->amount,
            'ord_num'   => $tran->ord_num,
            'appr_num'  => $tran->appr_num,
            'item_name' => $tran->item_name,
            'buyer_name'    => $tran->buyer_name,
            'buyer_phone'   => $tran->buyer_phone,
            'acquirer'      => $tran->acquirer,
            'issuer'        => $tran->issuer,
            'card_num'      => $tran->card_num,
            'installment'   => $tran->installment,
            'trx_dttm'      => $tran->trx_dt." ".$tran->trx_tm,
            'is_cancel'     => $tran->is_cancel,
            'temp'          => $temp,
        ];
        if($tran->is_cancel)
        {
            $params['amount'] *= -1;
            $params['cxl_dttm'] = $tran->cxl_dt." ".$tran->cxl_tm;
            $params['ori_trx_id'] = $tran->ori_trx_id;
        }
        return $params;
    }

    static public function notiSender($url, $tran, $temp='')
    {
        $headers = [
            'Content-Type'  => 'application/json',
            'Accept' => 'application/json',
        ];
        $params = self::getNotiSendFormat($tran, $temp);
        return post($url, $params, $headers);
    }

    static public function notiSenderWrap($id)
    {
        $fail_res    = [];
        $success_res = [];
        $trans = Transaction::join('noti_urls', 'transactions.mcht_id', '=', 'noti_urls.mcht_id')
            ->where('noti_urls.is_delete', false)
            ->where('transactions.id', $id)
            ->get(['transactions.*', 'noti_urls.send_url']);

        if(count($trans) === 0)
            $fail_res[] = '#'.$id.":".__("validation.not_found_obj")."<br>";
        else
        {
            foreach($trans as $tran)
            {
                $tran->retry_count = 0;
                $res = self::notiSender($tran->send_url, $tran, '');
                self::save($res, $tran);

                if($res['code'] === 201)
                    $success_res[] = '#'.$id;
                else
                    $fail_res[] = '#'.$id.":".$res['body']."<br>";
            }    
        }
        return [$success_res, $fail_res];
    }
}
