<?php
namespace App\Http\Controllers\FirstSettlement;

use App\Models\Merchandise;
use Illuminate\Support\Facades\Redis;

class NotiSenderBase
{
    static public function getMcht($tran)
    {
        $key_name = 'mcht-info-'.$tran['mcht_id'].'-'.$tran['pmod_id'];
        $mcht = Redis::get($key_name, 'EX', 300);
        if($mcht)
            return json_decode($mcht, true);
        else
        {
            $mcht = Merchandise::join('payment_modules', 'merchandise.id', '=', 'payment_modules.mcht_id')
                ->where('merchandise.id', $tran['mcht_id'])
                ->where('payment_modules.id', $tran['pmod_id'])
                ->first([
                    'merchandise.id', 'merchandise.user_name', 'merchandise.mcht_name',
                    'payment_modules.sign_key'
                ])
                ->toArray();
            Redis::set(json_encode($mcht), 'over', 'EX', 300);
            return $mcht;
        }
    }

    static public function getSignature($tran)
    {
        $timestamp = time();
        if($tran['is_cancel'])
        {
            $mcht = self::getMcht($tran);
            $sign_key = $mcht['sign_key'];
        }
        else
            $sign_key = $tran['sign_key'];

        if($sign_key)
        {
            $signature = hash('sha256', 'sign_key='.$sign_key.'&timestamp='.$timestamp);
            return [$timestamp, $signature];
        }
        else
            return ['', ''];
    }
}
