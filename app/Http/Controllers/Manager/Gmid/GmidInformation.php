<?php
namespace App\Http\Controllers\Manager\Gmid;

use Illuminate\Support\Facades\Redis;
use App\Models\Merchandise;

class GmidInformation
{
    static public function getMchtIds($g_mid)
    {
        $key_name = "$g_mid-id-infos";
        $mcht_ids = Redis::get($key_name);
        if($mcht_ids)
            return json_decode($mcht_ids, true);
        else
        {
            if($g_mid === '')
                $mcht_ids = [];
            else
                $mcht_ids = Merchandise::where('is_delete', false)->where('g_mid', $g_mid)->pluck('id')->all();
            Redis::set($key_name, json_encode($mcht_ids), 'EX', 60);
            return $mcht_ids;
        }
    }
}
