<?php
namespace App\Http\Controllers\FirstSettlement;

use App\Models\Merchandise;
use Illuminate\Support\Facades\Redis;

class NotiSenderBase
{
    static public function getMcht($tran)
    {
        $key_name = 'mcht-info-'.$tran['mcht_id'].'-'.$tran['pmod_id'];
        $mcht = Redis::get($key_name);
        if($mcht !== null)
            return json_decode($mcht, true);
        else
        {
            $mcht = Merchandise::join('payment_modules', 'merchandises.id', '=', 'payment_modules.mcht_id')
                ->where('merchandises.id', $tran['mcht_id'])
                ->where('payment_modules.id', $tran['pmod_id'])
                ->first([
                    'merchandises.id', 'merchandises.user_name', 'merchandises.mcht_name',
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
        $mcht = self::getMcht($tran);
        $sign_key = $mcht['sign_key'];

        if($sign_key)
        {
            $signature = hash('sha256', 'sign_key='.$sign_key.'&timestamp='.$timestamp."&mid=".$tran['mid']);
            return [$timestamp, $signature];
        }
        else
            return ['', ''];
    }
}
