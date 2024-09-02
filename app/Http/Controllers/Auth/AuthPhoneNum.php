<?php
namespace App\Http\Controllers\Auth;

use App\Enums\AuthLoginCode;
use App\Http\Traits\Models\EncryptDataTrait;
use App\Models\Merchandise;
use Illuminate\Support\Facades\Redis;
use Carbon\Carbon;

class AuthPhoneNum
{
    use EncryptDataTrait;

    static function tokenDecode($token)
    {
        $inst = new AuthPhoneNum();
        return $inst->aes256_decode($token);
    }

    // 휴대폰 토큰 검증
    static function validate($token)
    {
        if($token === '')
            return AuthLoginCode::REQUIRE_PHONE_AUTH->value;
        else
        {
            $auth_info = self::tokenDecode($token);
            if($auth_info)
            {
                $token_info = json_decode($auth_info, true);
                if(isset($token_info['phone_num']) && isset($token_info['verify_code']) && isset($token_info['verify_date']))
                    return AuthLoginCode::SUCCESS->value;
                else
                    return AuthLoginCode::WRONG_ACCESS->value;
            }
            else
                return AuthLoginCode::WRONG_ACCESS->value;
        }
    }

    static public function limitGet($mcht_id, $phone_num)
    {
        $over_key_name = "phone-auth-limit-over-".$mcht_id.":".$phone_num;
        return Redis::get($over_key_name);
    }

    static public function limitSet($mcht_id, $phone_num)
    {
        $end_time = $e_tm->diffInSeconds(Carbon::now());
        $over_key_name = "phone-auth-limit-over-".$mcht_id.":".$phone_num;
        Redis::set($over_key_name, 'over', 'EX', $end_time);
    }

    static public function countGet($mcht_id, $phone_num)
    {
        $count_key_name = "phone-auth-limit-count-".$mcht_id.":".$phone_num;
        return ((int)Redis::get($count_key_name)) + 1;
    }

    static public function countSet($mcht_id, $phone_num, $try_count)
    {
        $end_time = $e_tm->diffInSeconds(Carbon::now());
        $count_key_name = "phone-auth-limit-count-".$mcht_id.":".$phone_num;
        Redis::set($count_key_name, $try_count, 'EX', $end_time); 
    }
    
    // 휴대폰 인증허용회수 검증
    static public function limitValidate($brand, $phone_num, $mcht_id)
    {
        if($brand['pv_options']['paid']['use_pay_verification_mobile'])
        {
            $is_over = self::limitGet($mcht_id, $phone_num);
            if($is_over)
                return false;
            else
            {
                $mcht = Merchandise::where('id', $mcht_id)->first();
                if($mcht)
                {
                    if($mcht->phone_auth_limit_count)
                    {
                        [$time_type, $s_tm, $e_tm] = self::payDisableTimeType($mcht->phone_auth_limit_s_tm, $mcht->phone_auth_limit_e_tm);
                        if($time_type > 0)
                        {
                            $try_count = self::countGet($mcht_id, $phone_num);                            
                            self::countSet($mcht_id, $phone_num, $try_count);

                            if($mcht->phone_auth_limit_count < $try_count)
                            {
                                self::limitSet($mcht_id, $phone_num);
                                return false;
                            }
                            else
                                return true;
                        }
                    }
                }
            }
        }
        return true;
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
}
