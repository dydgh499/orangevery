<?php
namespace App\Http\Controllers\Option;

use App\Http\Controllers\Option\PaymentTimeValidate;
use App\Http\Controllers\Option\PayValidate;
use App\Models\PaymentModule;
use App\Models\Merchandise;
use App\Models\Salesforce;
use App\Models\Transaction;
use App\Models\Withdraws\VirtualAccountWithdraw;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class WithdrawValidate extends PaymentTimeValidate
{
    static public function getTrxNumFormat($code, $head, $withdraw_count)
    {
        $body = str_pad($withdraw_count+1, 11 - strlen($head), "0", STR_PAD_LEFT);
        return (int)$code.$head.$body;
    }

    // 지급보류 검증
    static public function settleHoldValidate($mcht)
    {
        if($mcht->settle_hold_s_dt)
        {
            if (Carbon::now()->greaterThan($mcht->settle_hold_s_dt))
            {
                return [
                    'code' => false,
                    'message' => "지급보류: ".$mcht->settle_hold_reason,
                    ];
            }
        }
        return [
            'code' => true,
            'message' => "",
        ];
    }

    static public function timeoutValidate($histories, $timeout_codes)
    {
        $is_success = false;
        $is_timeout = false;
        $trx_num = '';
        foreach($histories as $history)
        {
            if(in_array($history->result_code, $timeout_codes))
            {
                $is_timeout = true;
                $trx_num = $history->trans_seq_num;
            }
            if(in_array($history->result_code, ["0000", "0050"]) && $history->request_type === 6170)
            {
                $is_success = true;
                $trx_num = $history->trans_seq_num;
            }
        }
        if($is_success)
            return ['PV481', '이미 입금처리가 된 건입니다.', $trx_num];
        else if($is_timeout)
            return ['PV482', '타임아웃 발생건, 재조회 필요', $trx_num];
        else
            return ['0000', '', $trx_num];
    }

    static public function acctBankCodeValidate($acct_bank_code)
    {
        if($acct_bank_code === null)
            return ['PV485', '은행코드를 매칭할 수 없습니다.'];
        else
            return ['0000', ''];
    }

    static public function amountValidate($amount)
    {
        return ($amount) < 1 ? false : true;
    }

    static private function getSelectColumn()
    {
        return [
            'payment_modules.id',
            'payment_modules.mcht_id',
            'payment_modules.brand_id',
            'merchandises.settle_hold_s_dt',
            'merchandises.settle_hold_reason',
            'merchandises.sales5_id',
        ];
    }

    static public function getPaymentModule($pmod_id)
    {
        return PaymentModule::join('merchandises', 'payment_modules.mcht_id', '=', 'merchandises.id')
            ->where('payment_modules.id', $pmod_id)
            ->where('merchandises.is_delete', false)
            ->where('payment_modules.is_delete', false)
            ->first(self::getSelectColumn());
    }

    static public function getMerchandise($mcht_id)
    {
        return Merchandise::join('payment_modules', 'merchandises.id', '=' , 'payment_modules.mcht_id')
                ->where('payment_modules.mcht_id', $mcht_id)
                ->where('merchandises.is_delete', false)
                ->where('payment_modules.is_delete', false)
                ->first(self::getSelectColumn());
    }

    // 출금 검증
    static public function defaultValidate($pmod, $amount)
    {
        [$code, $message] = ['0000', ''];
        $result = self::settleHoldValidate($pmod);
        if($result['code'] === false)
            return ['PV480', $result['message']];
        if(self::specifiedTimeDisableValidate($pmod, 1) === false)
            [$code, $message] = ['PV424', '지금은 이체할 수 없습니다.'];
        else if(self::amountValidate($amount) === false)
            [$code, $message] = ['PV483', '이체 금액이 1원 미만입니다.'];
        else if(PayValidate::systemInspectionValidate() === false)
            [$code, $message] = ['PV427', '시스템 점검시간입니다.(06:00 ~ 06:05)'];

        return [$code, $message];
    }
}
