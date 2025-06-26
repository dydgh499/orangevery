<?php
namespace App\Http\Controllers\Option;

use App\Http\Controllers\Ablilty\BrandInfo;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\DB;
use App\Models\Transaction;
use Carbon\Carbon;

class PaymentTimeValidate
{
    static public function isHoliday($brand_id)
    {
        $now = Carbon::now();
        $now_dt = $now->format('Y-m-d');
        $holidays = explode(',', Transaction::getHolidays($brand_id, $now_dt, $now_dt));
        return in_array($now_dt, $holidays) ? true : false;
    }

    static public function isWeekend()
    {
        $now = Carbon::now();
        return $now->isWeekend() ? true : false;
    }

    static private function payDisableTimeType($s_tm, $e_tm)
    {
        $cond_1 = $s_tm && $e_tm;
        $cond_2 = $s_tm !== $e_tm;
        if ($cond_1 && $cond_2)
        {
            $current_time = Carbon::now();

            $start_time_today = Carbon::today()->setTimeFromTimeString($s_tm);
            $end_time_today = Carbon::today()->setTimeFromTimeString($e_tm);

            $start_time_yesterday = Carbon::yesterday()->setTimeFromTimeString($s_tm);
            $end_time_tomorrow = Carbon::tomorrow()->setTimeFromTimeString($e_tm);


            if($start_time_today->lessThan($end_time_today))
            {   //오늘 ~ 오늘 05:00 ~ 12:00
                if ($current_time->between($start_time_today, $end_time_today))
                    return [3, $start_time_today, $end_time_today];
            }
            else
            {
                //어제 ~ 오늘 23:00 ~ 05:00
                if($current_time->between($start_time_yesterday, $end_time_today))
                    return [1, $start_time_yesterday, $end_time_today];
                //오늘 ~ 다음날 23:00 ~ 05:00
                if ($current_time->between($start_time_today, $end_time_tomorrow))
                    return [2, $start_time_today, $end_time_tomorrow];
            }
        }
        return [0, '', ''];
    }

    // 결제 - 시간 검증
    static public function payDisableTimeValidate($pmod)
    {
        [$time_type, $s_tm, $e_tm] = self::payDisableTimeType($pmod->pay_disable_s_tm, $pmod->pay_disable_e_tm);
        if($time_type > 0)
            return false;
        else
            return true;
    }

    // 지정시간 결제 한도 하향 검증
    static public function specifiedTimeSinglePaymentValidate($pmod, $amount)
    {
        $key_name = "spcified-time-single-payment".$pmod->id;
        if($pmod->specified_time_disable_limit)
        {
            [$time_type, $s_tm, $e_tm] = self::payDisableTimeType($pmod->single_payment_limit_s_tm, $pmod->single_payment_limit_e_tm);
            if($time_type > 0)
            {
                if((int)$amount > (int)$pmod->specified_time_disable_limit * 10000)
                    return false;
            }
        }
        return true;
    }

    // 결제 - 계약일 검증
    static public function contractDateValidate($pmod)
    {
        // 브라이트픽스 예
        if(in_array($pmod->id, [54967, 63874, 66424, 68301]))
            return true;
        if($pmod->contract_s_dt && $pmod->contract_e_dt)
        {
            if(Carbon::now()->between(Carbon::parse($pmod->contract_s_dt), Carbon::parse($pmod->contract_e_dt)))
                return true;
            else
                return false;
        }
        return true;
    }

    // 지정시간 금지 한도 검증(결제, 이체)
    static public function specifiedTimeDisableValidate($pmod, $disable_type)
    {
        $key_name = "spcified-time-disable-$disable_type-".$pmod->id;
        $brand = BrandInfo::getBrandById($pmod->brand_id);
        if($brand['pv_options']['paid']['use_specified_limit'])
        {
            $is_over = Redis::get($key_name);
            if($is_over)
                return false;
            else
            {
                $disable_option = DB::connection('onetest')
                    ->table('specified_time_disable_payments')
                    ->where('mcht_id', $pmod->mcht_id)
                    ->where('disable_type', $disable_type)
                    ->first();
                if($disable_option)
                {
                    [$time_type, $s_tm, $e_tm] = self::payDisableTimeType($disable_option->disable_s_tm, $disable_option->disable_e_tm);
                    if($time_type > 0)
                    {
                        Redis::set($key_name, 'over', 'EX', 300);
                        return false;
                    }
                }
            }
        }
        return true;
    }
}
