<?php
namespace App\Http\Controllers\Option;

use App\Http\Controllers\Option\PaymentTimeValidate;
use Illuminate\Support\Facades\Redis;
use App\Models\Transaction;
use App\Models\Log\FailTransaction;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PayValidate
{
    // 시스템 점검시간 검증
    static public function systemInspectionValidate()
    {
        $start_time = Carbon::createFromTime(6, 0, 0); // 06:00:00
        $end_time = Carbon::createFromTime(6, 5, 0);   // 06:05:00

        if (Carbon::now()->between($start_time, $end_time))
            return false;
        else
            return true;
    }

    // 결제 - 한도 검증
    static public function payLimitValidate($db, $pmod, $amount)
    {
        $result = ['code'=>true, 'try'=>0, 'limit'=>0, 'type'=>''];
        $pay_single_limit = $pmod->pay_single_limit * 10000;
        $pay_d_limit = $pmod->pay_day_limit * 10000;
        $pay_m_limit = $pmod->pay_month_limit * 10000;
        $pay_y_limit = $pmod->pay_year_limit * 10000;

        if($pay_d_limit || $pay_m_limit || $pay_y_limit || $pay_single_limit)
        {
            $trans = $db->table('transactions')
                ->where('pmod_id', $pmod->id)
                ->where('brand_id', $pmod->brand_id)
                ->where('trx_at', '>=', Carbon::now()->subYear()->format('Y-m-d 00:00:00'))
                ->get(['amount', 'trx_dt']);

            $sum_today = $trans->filter(function ($transaction) {
                return Carbon::parse($transaction->trx_dt)->isToday();
            })->sum('amount') + $amount;

            $sum_month = $trans->filter(function ($transaction) {
                return Carbon::parse($transaction->trx_dt)->greaterThanOrEqualTo(Carbon::now()->startOfMonth());
            })->sum('amount') + $amount;

            $sum_year = $trans->filter(function ($transaction) {
                return Carbon::parse($transaction->trx_dt)->greaterThanOrEqualTo(Carbon::now()->startOfYear());
            })->sum('amount') + $amount;

            if($pay_single_limit && (int)$amount > $pay_single_limit)
            {
                $result['code'] = false;
                $result['try'] = $amount;
                $result['limit'] = $pay_single_limit;
                $result['type'] = '건';
            }
            else if($pay_d_limit && $sum_today > $pay_d_limit)
            {
                $result['code'] = false;
                $result['try'] = $sum_today;
                $result['limit'] = $pay_d_limit;
                $result['type'] = '일';
            }
            else if($pay_m_limit && $sum_month > $pay_m_limit)
            {
                $result['code'] = false;
                $result['try'] = $sum_month;
                $result['limit'] = $pay_m_limit;
                $result['type'] = '월';
            }
            else if($pay_y_limit && $sum_year > $pay_y_limit)
            {
                $result['code'] = false;
                $result['try'] = $sum_year;
                $result['limit'] = $pay_y_limit;
                $result['type'] = '연';
            }
        }
        return $result;
    }

    // 취소/결제 - 사용가능여부 검증
    static public function merchantStatusValidate($pmod)
    {
        return $pmod->merchant_status === 0 ? true : false;
    }
    // 입금 수수료 검증
    static public function settleFeeValidate($pmod, $amount)
    {
        return $amount < $pmod->settle_fee ? false : true;
    }

    // 결제타입 검증
    static public function payLimitTypeValidate($pmod)
    {
        if($pmod->pay_limit_type)
        {
            if($pmod->pay_limit_type === 1 && PaymentTimeValidate::isWeekend())
                return false;
            if($pmod->pay_limit_type === 2 && PaymentTimeValidate::isHoliday($pmod->brand_id))
                return false;
            if($pmod->pay_limit_type === 3 && (PaymentTimeValidate::isWeekend() || PaymentTimeValidate::isHoliday($pmod->brand_id)))
                return false;
            else
                return true;
        }
        else
            return true;
    }

    // 결제 공통 검증
    static public function validate($db, $pmod, $noti)
    {
        [$code, $message] = ['0000', ''];
        $pg_name = getPGType($pmod->pg_type);

        $result = self::payLimitValidate($db, $pmod, $noti['amount']);
        if($result['code'] === false)
            [$code, $message] = ['PV408',$result['type'].' 결제한도 '.number_format($result['limit']).'원 초과(총 시도액: '.number_format($result['try']).'원)', $pg_name];
        else if(self::merchantStatusValidate($pmod) === false)
            [$code, $message] = ['PV412', '사용불가 가맹점 입니다.', $pg_name];
        else if(PaymentTimeValidate::payDisableTimeValidate($pmod) === false)
            [$code, $message] = ['PV409', '지금은 결제할 수 있는 시간이 아닙니다.', $pg_name];
        else if(self::settleFeeValidate($pmod, $noti['amount']) === false)
            [$code, $message] = ['PV415', '결제금액이 너무 낮습니다.', $pg_name];
        else if(PaymentTimeValidate::specifiedTimeDisableValidate($pmod, 0) === false)
            [$code, $message] = ['PV424', '지금은 결제할 수 없습니다.', $pg_name];
        else if(PaymentTimeValidate::specifiedTimeSinglePaymentValidate($pmod, $noti['amount']) === false)
            [$code, $message] = ['PV425', '단건 결제한도를 초과', $pg_name];
        else if(PaymentTimeValidate::contractDateValidate($pmod) === false)
            [$code, $message] = ['PV426', '계약기간이 만료되었습니다.', $pg_name];
        else if(self::systemInspectionValidate() === false)
            [$code, $message] = ['PV427', '시스템 점검시간입니다.(06:00 ~ 06:05)', $pg_name];
        else if(self::payLimitTypeValidate($pmod) === false)
            [$code, $message] = ['PV428', '오늘은 결제할 수 없습니다.', $pg_name];
        if($code !== '0000')
            $code = self::addFailTransaction($pmod, $noti, 0, $code, $message);
        return [$code, $message, $pg_name];
    }

    static public function addFailTransaction($pmod, $noti, $is_cancel, $code, $message)
    {
        if(strpos($code, 'PV') === false)
            $code = str_replace('PV', env('ERROR_PREFIX', "PV"), $code);
        $res = FailTransaction::create([
            'brand_id' => $pmod->brand_id,
            'pmod_id' => $pmod->id,
            'pg_id' => $pmod->pg_id,
            'ps_id' => $pmod->ps_id,
            'module_type' => $pmod->module_type,
            'amount' => $noti['amount'],
            'trx_dt' => date('Y-m-d'),
            'trx_tm' => date('H:i:s'),
            'is_cancel' => $is_cancel,
            'result_cd' => $code,
            'result_msg' => Str::limit($message, 250, '...'),
        ]);
        return $code;
    }

    static public function getShoppingMallFormat($request)
    {
        return [
            'addr'          => $request->input('addr',''),
            'detail_addr'   => $request->input('detail_addr',''),
            'option_groups' => $request->input('option_groups',''),
            'note'          => $request->input('note',''),
        ];
    }

    static public function getDefaultPayFormat($pmod, $request)
    {
        $data = array_merge(self::getShoppingMallFormat($request), [
            'mcht_id'   => $pmod->mid,
            'tid'       => $pmod->tid,
            'pg_id'     => $pmod->pg_id,
            'amount'    => (int)$request->amount,
            'item_name' => $request->item_name,
            'ord_num'   => $request->ord_num,
            'buyer_name' => $request->input('buyer_name', ''),
            'buyer_phone' => $request->input('buyer_phone', ''),
            'installment' => sprintf("%02d", $request->input('installment', '0')),
            'temp'      => $request->input('temp',''),
        ]);

		return $data;
    }

    static public function getPayModule($db, $mid, $tid, $pmod_id)
    {
        $mid = $mid === null ? "" : $mid;
        $tid = $tid === null ? "" : $tid;
        $pmod_id = $pmod_id === null ? "" : $pmod_id;
        $pay_key = request()->header('Authorization', request()->input('pay_key', ''));

        $query = $db->table('payment_modules')
                //->join('merchandises', 'payment_modules.mcht_id', '=', 'merchandises.id')
                ->join('payment_gateways', 'payment_modules.pg_id', '=', 'payment_gateways.id')
                ->where('payment_modules.is_delete', false);

        if($mid === "" && $tid === "" && $pmod_id === "" && $pay_key === "")
            return [];
        else
        {
            if($mid !== '')
                $query = $query->where('payment_modules.mid', $mid);
            if($tid !== '')
                $query = $query->where('payment_modules.tid', $tid);
            if($pay_key !== '')
                $query = $query->where('payment_modules.pay_key', $pay_key);
            if($pmod_id !== '')
                $query = $query->where('payment_modules.id', $pmod_id);
            return $query->first([
                'payment_modules.*'
            ]);
        }
    }

    static public function getPG($noti, $pmod, $pay_type, $ver, $pg_name)
    {
        try
        {
            $pmod   = json_decode(json_encode($pmod), true);
            $path   = "App\Http\Controllers\PG\Payment\\".$pg_name;
            return new $path($noti, $pmod, $pay_type, $ver);
        }
        catch(Throwable $e)
        {   // pg사 발견못함
            logging([], $e->getMessage());
            return null;
        }
    }
}
