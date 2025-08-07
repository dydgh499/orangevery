<?php

namespace App\Http\Controllers\Manager\Transaction;

use App\Http\Controllers\Utils\Comm;
use App\Http\Controllers\Ablilty\Ablilty;

/**
 * @group Transaction API
 *
 * 거래 API 입니다.
 */
class TransactionAPI extends TransactionTest
{
    static public function getPrefixName($prefix_type)
    {
        if($prefix_type === 1)
            return "H";
        else if($prefix_type === 4)
            return "BP";
        else if($prefix_type === 41)
            return "BC";
        else if($prefix_type === 42)
            return "BD";
        else
            return null;
    }

    static public function createOrdNum($pmod_id, $prefix_type)
    {
        $milisec = (string)(int)round(microtime(true) * 1000);
        $prefix = self::getPrefixName($prefix_type);
        if($prefix)
            return $pmod_id.$prefix.$milisec;
        else
            return null;
    }

    // 결제취소
    static public function payCancel($data, $pay_key)
    {
        return Comm::post(env('PAY_URL', 'http://localhost:81').'/api/v2/pay/cancel', $data, [
            'Authorization' => $pay_key
        ]);
    }
    
    // 수기결제
    static public function handPay($data, $pay_key)
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
        $data['ord_num'] = self::createOrdNum($data['pmod_id'], 1);
        $url = env('PAY_URL', 'http://localhost:81').'/api/v2/pay/hand';

        if(Ablilty::isAppLocal())
        {
            return [
                'code' => 200,
                'body' => self::getTestPayResult($data)
            ];
        }
        else
        {
            return Comm::post($url, $data, [
                'Authorization' => $pay_key
            ]);
        }
    }
        
    static public function billPay($data, $pay_key)
    {
        $data['ord_num'] = self::createOrdNum($data['pmod_id'], 4);
        $url = env('PAY_URL', 'http://localhost:81').'/api/v2/pay/bill-key/hand';

        if(Ablilty::isAppLocal())
        {
            return [
                'code' => 200,
                'body' => self::getTestPayResult($data)
            ];
        }
        else
        {
            return Comm::post($url, $data, [
                'Authorization' => $pay_key
            ]);
        }
    }

    static public function billCreate($data, $pay_key)
    {
        $data['ord_num'] = self::createOrdNum($data['pmod_id'], 41);
        $url = env('PAY_URL', 'http://localhost:81').'/api/v2/pay/bill-key';

        if(Ablilty::isAppLocal())
        {
            return [
                'code' => 200,
                'body' => self::getTestBillCreateResult($data)
            ];
        }
        else
        {
            return Comm::post($url, $data, [
                'Authorization' => $pay_key
            ]);
        }
    }

    static public function billRemove($data, $pay_key)
    {
        $data['ord_num'] = self::createOrdNum($data['pmod_id'], 42);
        $url = env('PAY_URL', 'http://localhost:81').'/api/v2/pay/bill-key';

        if(Ablilty::isAppLocal())
        {
            return [
                'code' => 200,
                'body' => self::getTestBillDeleteResult($data)
            ];
        }
        else
        {
            return Comm::destroy($url, $data, [
                'Authorization' => $pay_key
            ]);
        }
    }
}
