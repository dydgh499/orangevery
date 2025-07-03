<?php

namespace App\Http\Controllers\Manager\Transaction;

use App\Models\Pay\BillKey;

/**
 * @group Transaction API
 *
 * 거래 API 입니다.
 */
class WithdrawTest
{
    static public function getTestWithdrawResult()
    {
            return [
            'result_cd'  => '0000',
            'result_msg'=> '성공',
        ];
    }
    
    static public function getTestownerCheckResult($data)
    {
        return [
            'result'    => "0000", 
            'message'   => '정상입니다.', 
            'data'      => [],
        ];
    }
}
