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
}
