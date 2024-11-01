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
        return 1;
    }
}
