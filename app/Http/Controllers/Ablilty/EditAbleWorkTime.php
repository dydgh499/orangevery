<?php
namespace App\Http\Controllers\Ablilty;

use App\Http\Controllers\Ablilty\Ablilty;
use App\Http\Controllers\Ablilty\AbnormalConnection;
use App\Models\Service\ExceptionWorkTime;
use Illuminate\Support\Facades\Redis;
use Carbon\Carbon;

class EditAbleWorkTime
{
    static private function set($brand_id, $exception_times)
    {
        $key_name = "exception-work-time-".$brand_id;
        Redis::set($key_name, json_encode($exception_times), 'EX', 300);
    }

    static private function get($brand_id)
    {
        $key_name = "exception-work-time-".$brand_id;
        $exception_times = Redis::get($key_name);
        if($exception_times)
            return json_decode($exception_times, true);
        else
        {
            $yesterday = Carbon::now()->subDay(60)->format('Y-m-d 00:00:00');
            $tommrow = Carbon::now()->addDay(60)->format('Y-m-d 23:59:59');
            $exception_times = ExceptionWorkTime::where('brand_id', $brand_id)
                ->where('work_s_at', '>=', $yesterday)
                ->where('work_e_at', '<=', $tommrow)
                ->get()
                ->toArray();
            self::set($brand_id, $exception_times);
            return $exception_times;
        }
    }

    static private function isExceptionCustom()
    {
        // 브라이트픽스 총판 예외 : kim5150, 2024-09-09부터 적용
        if(Ablilty::isSalesforce(request()) && request()->user()->id === 9393 && in_array(request()->ip(), ['58.225.69.144', '221.140.168.13']))
            return true;
        else if(Ablilty::isDevOffice(request()) && in_array(request()->user()->brand_id, [18, 35]))
            return true;
        else
            return false;
    }

    static private function isExceptionOperator()
    {
        $exception_times = self::get(request()->user()->brand_id);
        foreach($exception_times as $exception_time)
        {
            $work_s_at = Carbon::createFromFormat('Y-m-d H:i:s', $exception_time['work_s_at'] );
            $work_e_at = Carbon::createFromFormat('Y-m-d H:i:s', $exception_time['work_e_at'] );
            if(Carbon::now()->between($work_s_at, $work_e_at) && $exception_time['oper_id'] === request()->user()->id && request()->user()->tokenCan(35))
                return true;
        }
        return false;
    }

    static function validate()
    {
        // 21시 ~ 06시까지는 가맹점, 영업라인, 운영자, 결제모듈, 금융 VAN, 브랜드 추가/수정 불가
        $now = Carbon::now();
        if ($now->hour >= 21 || $now->hour < 6) 
        {
            if(env('APP_ENV') === 'local')
                return true;
            else if(request()->user()->brand_id === 30)
                return true;
            else if(self::isExceptionOperator())
                return true;
            else if(self::isExceptionCustom())
                return true;
            else
            {
                AbnormalConnection::tryCannotAllowTime();
                return false;    
            }
        }
        else
            return true;
    }
}
