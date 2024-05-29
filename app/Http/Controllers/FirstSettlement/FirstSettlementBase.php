<?php
namespace App\Http\Controllers\FirstSettlement;

use App\Models\Merchandise;
use Illuminate\Support\Facades\Redis;

class FirstSettlementBase
{
    static protected function getMcht($mcht_id)
    {
        $key_name = 'mcht-info-'.$mcht_id;
        $mcht = Redis::get($key_name, 'EX', 300);
        if($mcht)
            return json_decode($mcht, true);
        else
        {
            $mcht = Merchandise::where('id', $mcht_id)
                ->first(['id', 'user_name', 'mcht_name'])
                ->toArray();
            Redis::set(json_encode($mcht), 'over', 'EX', 300);
            return $mcht;
        }
    }
}
