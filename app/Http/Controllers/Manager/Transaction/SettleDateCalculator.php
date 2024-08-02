<?php
namespace App\Http\Controllers\Manager\Transaction;
use App\Models\Transaction;
use Carbon\Carbon;

class SettleDateCalculator
{
    static private function getHolidays($brand_id ,Carbon $settle_dt)
    {
        //조회일로부터 M-1, M+1 공휴일 조회
        $holiday_s_dt = $settle_dt->copy()->subMonthNoOverflow(1)->startOfMonth()->format('Y-m-d');
        $holiday_e_dt = $settle_dt->copy()->addMonthNoOverflow(1)->startOfMonth()->format('Y-m-d');
        return explode(',', Transaction::getHolidays($brand_id, $holiday_s_dt, $holiday_e_dt)); // 공휴일 문자열을 배열로 변환
    }

    static private function getWeekSettleDate($brand_id, string $trx_dt, int $settle_date_type, int $pg_settle_type)
    {
        $cb_trx_dt = Carbon::parse($trx_dt);
        if($settle_date_type === -2)
        {   // 주정산
            $settle_dt = $cb_trx_dt->next(Carbon::MONDAY)->next(Carbon::MONDAY);
        }
        else if($settle_date_type === -3)
        {   // 보름정산
            $settle_day = (int)$cb_trx_dt->format('d');
            if($settle_day <= 15)
                $settle_dt = $cb_trx_dt->endOfMonth();
            else
                $settle_dt = Carbon::parse($cb_trx_dt->addMonthNoOverflow(1)->format('Y-m-15'));
        }
        else if($settle_date_type === -4)
        {   // 월정산
            $settle_dt = Carbon::parse($cb_trx_dt->addMonthNoOverflow(1)->format('Y-m-15'));
        }
        else
        {
            error(['trx_dt'=>$trx_dt, 'settle_date_type'=>$settle_date_type], 'DETECT-WRONG-FORMAT-SETTLE-DT');
            exit;
        }

        $holidays = self::getHolidays($brand_id, $settle_dt);
        //pg_settle_type이 1인상태에서, settle_dt 가 주말이거나 공휴일에 포함되어있으면 하루씩 추가
        $danger_count = 0;
        while($pg_settle_type === 1 && ($settle_dt->isWeekend() || (in_array($settle_dt->format('Y-m-d'), $holidays))))
        {
            $settle_dt->addDay();

            $danger_count += 1;
            if($danger_count > 1000)
            {   //무한루프 방지
                error(['settle_dt'=>$settle_dt->format('Ymd'), 'DETECT-INFINITE-LOOP']);
                exit;
            }
        }
        return $settle_dt->format('Ymd');
    }

    static private function getDaySettleDate($brand_id, string $trx_dt, int $add_days, int $pg_settle_type)
    {
        $settle_dt = Carbon::parse($trx_dt);
        $holidays = self::getHolidays($brand_id, $settle_dt);
        if($pg_settle_type === 1)
        {
            $counter = 0;
            while ($counter < $add_days)
            {
                $settle_dt->addDay();
                // 주말이 아니고, 공휴일에 포함되지 않는 경우에만 카운터 증가
                if (!$settle_dt->isWeekend() && (empty($holidays) || !in_array($settle_dt->format('Y-m-d'), $holidays)))
                    $counter++;
            }
        }
        else
            $settle_dt->addDays($add_days);

        return $settle_dt->format('Ymd');
    }

    static public function getSettleDate($brand_id, $trx_dt, $settle_date_type, $pg_settle_type)
    {
        if($settle_date_type < -1)
            return self::getWeekSettleDate($brand_id, $trx_dt, (int)$settle_date_type, $pg_settle_type);
        else
            return self::getDaySettleDate($brand_id, $trx_dt, ((int)$settle_date_type)+1, $pg_settle_type);
    }
}
