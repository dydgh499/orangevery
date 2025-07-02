<?php

namespace App\Http\Controllers\Manager\Transaction;

use App\Http\Controllers\Ablilty\Ablilty;
use App\Http\Controllers\Ablilty\ActivityHistoryInterface;

use App\Models\Transaction;
use App\Models\Pay\NotiUrl;

use App\Http\Controllers\Utils\Comm;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

/**
 * @group Transaction API
 *
 * 거래 API 입니다.
 */
class TransactionAPI
{
    static private function getCancelTransData($request, $trans)
    {
        $cxl_dt = $request->cxl_dt;
        $cxl_tm = $request->cxl_tm;
        $ori_trx_id = $request->trx_id;
        $amount     = $request->amount;
        
        $cxl_query  = Transaction::where('brand_id', $request->user()->brand_id)
            ->where('trx_at', $request->trx_at)
            ->where('ori_trx_id', $ori_trx_id)
            ->where('is_cancel', 1);
        $cxl_seq = (clone $cxl_query)->count() + 1;
        $cancel  = (clone $cxl_query)->first([DB::raw("SUM(amount) AS cancel_amount")]);
        $total_cancel_amount = ((int)$cancel->cancel_amount * -1) + $amount;
        if($total_cancel_amount > $trans->amount)
        {
            $message = '부분취소 금액이 원거래 금액보다 높습니다.<br>(시도합계: '.number_format($total_cancel_amount).'원)<br>(원거래: '.number_format($trans->amount).'원)';
            return [false, $message, []];
        }
        else
        {
            $data = json_decode(json_encode($trans), true);
            unset($data['id']);
            $data['cxl_dt']     = $cxl_dt;
            $data['cxl_tm']     = $cxl_tm;
            $data['trx_at']     = $cxl_dt." ".$cxl_tm;
            $data['ori_trx_id'] = $ori_trx_id;
            $data['amount']     = $amount * -1;
            $data['is_cancel']  = 1;
            $data['cxl_seq']    = $cxl_seq; 

            $settle_ids = ['mcht_settle_id', 'sales0_settle_id', 'sales1_settle_id', 'sales2_settle_id', 'sales3_settle_id', 'sales4_settle_id', 'sales5_settle_id', 'dev_settle_id'];
            foreach($settle_ids as $settle_id)
            {
                $data[$settle_id] = null;
            }
            return [true, '', $data];
        }
    }

    // 취소매출 생성
    static public function createCancel($request)
    {
        $trans = DB::table('transactions')->where('id', $request->id)->first();
        if($trans)
        {
            [$code, $message, $data] = self::getCancelTransData($request, $trans);
            if($code)
            {
                try
                {
                    $add_res = app(ActivityHistoryInterface::class)->add('매출', new Transaction, $data, 'trx_id');
                    if($add_res)
                    {
                        return ['0000', '성공', []];
                    }
                    else
                        return [false, '시스템 에러입니다.', []];
                }
                catch(QueryException $ex)
                {
                    $message = $ex->getMessage();
                    if(str_contains($message, 'Duplicate entry'))
                        $message = '이미 같은 거래번호의 취소매출이 존재합니다.<br>해당 매출을 삭제하거나 거래번호를 수정한 후 다시 시도해주세요.';     
                    
                    return [false, $message, []];
                }
            }
            else
                return [false, $message, []];
        }
        else
            return [false, '거래데이터가 존재하지 않습니다.', []];
    }

    // 결제취소 생성
    static public function payCancel($data)
    {
        return Comm::post(env('NOTI_URL', 'http://localhost:81').'/api/v2/online/pay/cancel', $data);
    }
    
    // 수기결제
    static public function handPay($data)
    {
        $getYYMM = function($mmyy) {
            if(mb_strlen($mmyy, 'utf-8') == 4)
            {
                $first 	= substr($mmyy, 0, 2);
                $sec 	= substr($mmyy, 2, 2);
                return $sec.$first;
            }
            else
                return '';
        };

        $data['yymm'] = $getYYMM($data['yymm']); // mmyy to yymm
        $url = env('NOTI_URL', 'http://localhost:81').'/api/v2/online/pay/hand';
        return Comm::post($url, $data);
    }
    
    // 테스트
    static public function test()
    {
        $db_trans = Transaction::where('is_cancel', 1)
            ->where('trx_at', '>=', '2025-04-01 00:00:00')
            ->orderBy('id', 'desc')
            ->get();
        $i=0;
        $trans = json_decode(json_encode($db_trans), true);
        foreach($db_trans as $key => $tran)
        {
            
            $fields = [
                'brand_settle_amount',
            ];

            foreach ($fields as $field) 
            {
                $tran->{$field} = $trans[$key][$field];
            }
            $tran->save();
            $i++;
            echo $i."\n";
        }
    }
}
