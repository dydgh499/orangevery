<?php
namespace App\Http\Controllers\Manager\Settle;

use Illuminate\Support\Facades\Redis;
use Carbon\Carbon;

class AddDeduct
{
    static private function getAddDeductCount($key_name)
    {
        return (int)Redis::get($key_name);
    }

    static private function setAddDeductCount($key_name, $count)
    {
        $limit_sec = (Carbon::now()->addDays(1))->diffInSeconds(Carbon::now());
        Redis::set($key_name, $count, 'EX', $limit_sec);
    }

    static private function calcAddDeductCount($key_name, $plus)
    {
        $count = self::getAddDeductCount($key_name) + $plus;
        self::setAddDeductCount($key_name, $count);
    }

    static private function countValidate($request, $key_name, $max_count)
    {
        $count  = self::getAddDeductCount($key_name);
        if($count >= $max_count)
            return false;
        else
        {
            self::calcAddDeductCount($key_name, 1);
            return true;
        }
    }

    static private function amountValidate($request, $amount_limit)
    {
        return abs((int)$request->amount) < $amount_limit ? true : false;
    }

    static public function validate($request, $col)
    {
        $base_key = 'add-deduct-brand-';
        if(in_array($request->user()->brand_id, [])) //12, 14
        {
            $amount_limit = 3000000;
            if(self::amountValidate($request, $amount_limit) === false)
                return -3;
            //
            $s_dt = Carbon::createFromFormat('Y-m-d H:i:s', '2024-06-13 09:30:00');
            $e_dt = Carbon::createFromFormat('Y-m-d H:i:s', '2024-06-13 12:00:00');
            $brand_limit = Carbon::now()->between($s_dt, $e_dt) ? 300 : 10;

            $key_name = $base_key.$request->user()->brand_id;
            if(self::countValidate($request, $key_name, $brand_limit) === false)
                return -1;
            //
            $mcht_limit = 1;
            $key_name = $base_key.$request->id."-".($col === 'mcht_id' ? 10 : $request->user()->level);
            if(self::countValidate($request, $key_name, $mcht_limit) === false)
            {
                return -2;
            }
        }
        return 1;
    }
}
