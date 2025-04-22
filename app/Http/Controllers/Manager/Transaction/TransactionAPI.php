<?php

namespace App\Http\Controllers\Manager\Transaction;

use App\Http\Controllers\Ablilty\Ablilty;
use App\Http\Controllers\Ablilty\ActivityHistoryInterface;

use App\Models\Transaction;
use App\Models\Merchandise\NotiUrl;

use App\Http\Controllers\Manager\Transaction\SettleDateCalculator;
use App\Http\Controllers\Manager\Transaction\SettleAmountCalculator;

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
    static public function getNotiStatus($b_info, $data)
    {
         // -1 = 해당사항 없음,   0 = 발송대기 상태 ,   1 = 발송성공
         if($b_info['pv_options']['paid']['use_noti'])
         {
             $existCorrectNotiUrl = function($noti_urls, $content) {
                 foreach($noti_urls as $noti_url)
                 {
                     if($noti_url['mcht_id'] === $content['mcht_id'])
                     {   //취소인데 취소타입
                         $cxl_send_type  = ($content['is_cancel']        && $noti_url['send_type'] === 2);
                         //승인인데 승인타입
                         $appr_send_type = ($content['is_cancel'] === 0  && $noti_url['send_type'] === 1);
                         //전체 발송
                         $all_send_type  = $noti_url['pmod_id'] === -1;
                         if($all_send_type || $cxl_send_type || $appr_send_type)
                             return true;
                     }
                 }
                 return false;
             };
 
             $mcht_ids = array_unique($data['content']->pluck('mcht_id')->all());
             $noti_urls = NotiUrl::whereIn('mcht_id', $mcht_ids)
                 ->where('is_delete', false)
                 ->where('noti_status', true)
                 ->get()->toArray();
 
             foreach($data['content'] as $content)
             {
                 if($content['use_noti'])
                 {
                     if($existCorrectNotiUrl($noti_urls, $content))
                         $content['noti_status'] = count($content['notiSendHistories']) ? 1 : 0;
                     else
                         $content['noti_status'] = -1;
                 }
                 else
                     $content['noti_status'] = -1;
             }
         }
         return $data;
    }

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

            $data['settle_dt'] = SettleDateCalculator::getSettleDate($data['brand_id'], $data['cxl_dt'], $data['mcht_settle_type'], $data['pg_settle_type']);
            $settle_ids = ['mcht_settle_id', 'sales0_settle_id', 'sales1_settle_id', 'sales2_settle_id', 'sales3_settle_id', 'sales4_settle_id', 'sales5_settle_id', 'dev_settle_id'];
            foreach($settle_ids as $settle_id)
            {
                $data[$settle_id] = null;
            }
            [$data] = SettleAmountCalculator::setSettleAmount([$data]);
            return [true, '', $data];
        }
    }

    static private function setVirtualAccountHistory($request, $trans, $trans_id)
    {
        if($trans->mcht_settle_type === -1 && $request->va_id)
        {   // 실시간 일때
            $url = env('NOTI_URL', 'http://localhost:81').'/api/v2/realtimes/retry-settlement';
            $res = Comm::post($url, [
                'id'        => $trans_id,
                'va_id'     => $request->va_id,
                'user_id'   => $trans->mcht_id,
                'level'     => 10,
            ]);
            return $res;
        }
        else
        {
            return [
                'body' => [
                    'result_cd'     => '0000',
                    'result_msg'    => '성공하였습니다.',
                ]
            ];
        }
    }

    static public function createCancelDeposit($data)
    {        
        return Comm::post(env('NOTI_URL', 'http://localhost:81').'/api/v2/realtimes/cancel-deposit-settlement', $data);
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
                        $res = self::setVirtualAccountHistory($request, $trans, $add_res->id);
                        $code = $res['body']['result_cd'] === '0000' ? true : false;
                        return [$code, $res['body']['result_msg'], []];
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

        $data = $request->all();
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

        $trans = json_decode(json_encode($db_trans), true);
        [$data] = SettleAmountCalculator::setSettleAmount([$data]);
        foreach($db_trans as $key => $tran)
        {
            
            $fields = [
                'brand_settle_amount',
                'dev_realtime_settle_amount',
                'dev_settle_amount',
                'sales5_settle_amount',
                'sales4_settle_amount',
                'sales3_settle_amount',
                'sales2_settle_amount',
                'sales1_settle_amount',
                'sales0_settle_amount',
                'mcht_settle_amount',
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
