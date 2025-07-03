<?php

namespace App\Http\Controllers\Manager\Transaction;

use App\Models\Pay\BillKey;

/**
 * @group Transaction API
 *
 * 거래 API 입니다.
 */
class TransactionTest
{
    static public function getTestPayResult($data)
    {
        return [
            'result_cd'  => '0000',
            'result_msg'=> '성공',
            'mid'       => "TESTMID",
            'tid'       => "",
            'amount'    => 5000,
            'ord_num'   => $data['ord_num'],
            'appr_num'  => "12341234",
            'pg_id'     => "123",
            'trx_id'    => $data['ord_num'],
            'acquirer'  => '기업',
            'issuer'    => '기업',
            'card_num'  => "1234******123434",
            'installment' => "00",
            'trx_at'    => date("Y-m-d H:i:s"),
            'method'    => '빌키',
            'is_cancel' => 0,
            'temp'      => "",
        ];
        /* TODO:: 제거
            'buyer_name'  => '홍길동',
            'buyer_phone' => "010000012312",
            'item_name' => '배달비',
        */
    }
    
    static public function getTestBillCreateResult($data)
    {
        $params = [
            'result_cd'  => '0000',
            'result_msg' => '성공',
            'id'         => 1,
            'bill_key'   => $data['ord_num'],
            'issuer'     => '기업',
            'trx_id'     => $data['ord_num'],
            'trx_dttm'   => date('Y-m-d H:i:s'),
            'ord_num'    => $data['ord_num'],
        ];
        BillKey::create([
            'pmod_id'   => $data['pmod_id'],
            'oper_id'   => request()->user()->id,
            'buyer_name' => request()->user()->nick_name,
            'buyer_phone' => request()->user()->phone_num,
            'issuer'    => $params['issuer'],
            'card_num'  => $data['card_num'],
            'bill_key'  => $params['bill_key'],
            'nick_name' => $data['nick_name'],
        ]);
        return $params;
    }

    static public function getTestBillDeleteResult($data)
    {
        return [
            'result_cd'  => '0000',
            'result_msg'=> '성공',
            'trx_dttm'   => date('Y-m-d H:i:s'),
            'ord_num'    => $data['ord_num'],
        ];
    }
}
