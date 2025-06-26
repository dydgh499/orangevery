<?php
namespace App\Http\Controllers\Option\Withdraw;

use App\Http\Controllers\Option\WithdrawValidate;
use App\Models\Withdraws\VirtualAccountWithdraw;

use App\Models\Salesforce;
use App\Models\Merchandise;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CMSTransactionLimitValidate extends WithdrawValidate
{

    // 출금금액 조회
    static public function getWithdrawAmount($user_ids, $timeout_codes)
    {
        $timeout_codes[] = '0000';
        $s_at = Carbon::now()->format('Y-m-d 00:00:00');
        $e_at = Carbon::now()->format('Y-m-d 23:59:59');
        $cols = [
            DB::raw("SUM(virtual_account_withdraws.trans_amount) AS total_amount")
        ];

        $trans = VirtualAccountWithdraw::join('virtual_account_histories', 'virtual_account_withdraws.va_history_id', '=' ,'virtual_account_histories.id')
            ->join('virtual_accounts', 'virtual_account_histories.va_id', '=', 'virtual_accounts.id')
            ->whereIn('virtual_account_withdraws.result_code', $timeout_codes)
            ->whereIn('virtual_accounts.user_id', $user_ids)
            ->where('virtual_account_histories.level', 10)
            ->where('virtual_account_withdraws.created_at', '>=', $s_at)
            ->where('virtual_account_withdraws.created_at', '<=', $e_at)
            ->first($cols);
        if($trans)
        {
            if($trans->total_amount)
                return (int)$trans->total_amount;
            else
                return 0;
        }
        return 0;
    }

    // 본사 한도 검증
    static public function limitSalesValidate($mcht, $amount, $timeout_codes)
    {
        if($mcht->sales5_id)
        {
            $sales = Salesforce::where('id', $mcht->sales5_id)->first();
            if($sales)
            {
                $sales = json_decode(json_encode($sales), true);
                if($sales['withdraw_holiday_limit'] || $sales['withdraw_business_limit'])
                {
                    $mcht_ids = Merchandise::where('sales5_id', $sales['id'])
                        ->where('brand_id', $mcht->brand_id)
                        ->pluck('id')
                        ->all();
                    $total_amount = self::getWithdrawAmount($mcht_ids, $timeout_codes) + $amount;
                    [$result, $try_amount, $limit_amount] = self::limitValidate($sales, $total_amount);
                    if($result)
                        return [true, ''];
                    else
                        return [false, '일간 이체한도 '.number_format($limit_amount).'원 초과(총 시도액: '.number_format($try_amount).'원)'];
                }
                else
                    return [true, ''];
            }
            else
            {
                error([],'본사 조회 실패');
                return [true, ''];
            }
        }
        else
            return [true, ''];
    }

    static public function accountLimitvalidate($mcht, $amount, $timeout_codes)
    {
        [$code, $message] = self::limitSalesValidate($mcht, $amount, $timeout_codes);
        if($code === false)
            return ['PV488', $message];
        else
            return parent::defaultValidate($mcht, $amount);
    }
}
