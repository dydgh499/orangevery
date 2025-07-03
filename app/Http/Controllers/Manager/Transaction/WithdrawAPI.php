<?php

namespace App\Http\Controllers\Manager\Transaction;

use App\Http\Controllers\Utils\Comm;
use App\Http\Controllers\Ablilty\Ablilty;

/**
 * @group Withdraw API
 *
 * 거래 API 입니다.
 */
class WithdrawAPI extends WithdrawTest
{
    static public function withdraw($data)
    {
        $url = env('WITHDRAW_URL', 'http://localhost:81').'/api/v2/realtimes/single-withdraw';
        if(Ablilty::isAppLocal())
        {
            return [
                'code' => 200,
                'body' => self::getTestWithdrawResult()
            ];
        }
        else
            return Comm::post($url, $data);
    }
    

    static public function ownerCheck($data)
    {
        $url = env('WITHDRAW_URL', 'http://localhost:81').'/api/v2/owner-check';
        if(Ablilty::isAppLocal())
        {
            return [
                'code' => 200,
                'body' => self::getTestownerCheckResult($data)
            ];
        }
        else
        {
            return Comm::post($url, $data, []);
        }
    }
}
