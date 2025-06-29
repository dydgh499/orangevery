<?php
namespace App\Http\Controllers\Option\Withdraw;

use App\Http\Controllers\Option\WithdrawValidate;
use App\Enums\RealtimeDepositCode;
use Illuminate\Support\Facades\DB;

class SettleWithdrawValidate extends WithdrawValidate
{
    static public function init($is_mcht)
    {
        $history_table  = $is_mcht ? 'settle_histories_merchandises' : 'settle_histories_salesforces';
        $table          = $is_mcht ? 'settle_histories_merchandises_deposits' : 'settle_histories_salesforces_deposits';
        $history_key    = $is_mcht ? 'settle_hist_mcht_id' : 'settle_hist_sales_id';
        $type           = $is_mcht ? RealtimeDepositCode::SETTLE_MCHT->value : RealtimeDepositCode::SETTLE_SALES->value;
        return [$history_table, $table, $history_key, $type];
    }

    static public function getTrxNum($is_mcht, $settle)
    {
        [$history_table, $table, $history_key, $type] = SettleWithdrawValidate::init($is_mcht);
        $withdraw_count = DB::connection('onetest')->table($table)
            ->where($history_key, $settle->id)
            ->whereBetween('created_at', [date('Y-m-d 00:00:00'), date('Y-m-d 23:59:59')])
            ->count();

        $code = $type;
        $head = date("d").$settle->id;
        return self::getTrxNumFormat($code, $head, $withdraw_count);
    }

    static public function addWithdraw($finance_van, $is_mcht, $settle, $code, $message, $trsc_no)
    {
        [$history_table, $table, $history_key, $type] = self::init($is_mcht);
        $datetime = date('Y-m-d H:i:s');
        $params = [
            'brand_id'      => $finance_van['brand_id'],
            'fin_id'        => $finance_van['id'],
            $history_key    => $settle->id,
            'request_type'  => '6170',
            'result_code'   => $code,
            'message'       => $message,
            'trans_seq_num' => $trsc_no,
            'created_at'    => $datetime,
            'updated_at'    => $datetime,
        ];
        return DB::connection('onetest')->table($table)->insertGetId($params);
    }

    static public function updateWithdraw($last_id, $is_mcht, $code, $message, $trans_seq_num='')
    {
        [$history_table, $table, $history_key, $type] = self::init($is_mcht);
        $params = [
            'result_code'   => $code,
            'message'       => $message,
        ];
        if($trans_seq_num !== '')
            $params['trans_seq_num'] = $trans_seq_num;
        return DB::connection('onetest')->table($table)->where('id', $last_id)->update($params);
    }


    static public function updateSettleInfo($is_mcht, $settle)
    {
        [$history_table, $table, $history_key, $type] = self::init($is_mcht);
        $params = [
            'deposit_amount' => $settle->settle_amount,
        ];
        DB::connection('onetest')->table($history_table)->where('id', $settle->id)->update($params);
    }

    static public function getSettleHistories($table, $history_key, $history_id, $history_table)
    {
        return DB::connection('onetest')->table($table)
                ->join($history_table, "$history_table.id", '=', "$table.$history_key")
                ->where($history_key, $history_id)
                ->get();
    }

    static public function validate($settle, $is_mcht, $amount, $acct_bank_code, $timeout_codes)
    {   // 은행정보 검증
        [$history_table, $table, $history_key, $type] = self::init($is_mcht);
        [$code, $message] = self::acctBankCodeValidate($acct_bank_code);
        if($code !== '0000')
            return [$code, $message, ''];
        else
        {
            if($is_mcht)
            {   // 출금 FDS 검증
                /* TODO
                $pmod = self::getMerchandise($settle->mcht_id);
                [$code, $message] = self::defaultValidate($pmod, $amount);
                if($code !== '0000')
                    return [$code, $message, ''];
                */
            }
            $histories = self::getSettleHistories($table, $history_key, $settle->id, $history_table);
            return self::timeoutValidate($histories, $timeout_codes);
        }
    }
}
