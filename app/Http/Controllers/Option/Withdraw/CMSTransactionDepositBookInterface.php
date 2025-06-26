<?php
namespace App\Http\Controllers\Option\Withdraw;

use App\Http\Controllers\Option\Withdraw\CMSTransactionValidate;
use App\Http\Controllers\Option\Withdraw\CMSTransactionWithdrawBookInterface;

use App\Models\Withdraws\VirtualAccount;
use App\Models\Withdraws\VirtualAccountHistory;
use App\Models\Withdraws\VirtualAccountWithdraw;

use App\Models\Log\SettleHistoryMerchandise;
use App\Models\Log\SettleHistorySalesforce;

use App\Models\PaymentModule;
use App\Models\Transaction;
use App\Models\Merchandise;
use App\Models\Salesforce;

use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use Carbon\Carbon;

class CMSTransactionDepositBookInterface extends CMSTransactionValidate
{
    static public function getUseVirtualAccountPayModule($merchants)
    {
        return $merchants->first(function ($mcht) {
            // 지갑 매핑여부, 실시간 사용여부, 실시간 여부
            return $mcht->va_id && $mcht->use_realtime_deposit && $mcht->settle_type === -1;
        });
    }

    static public function getVirtualAccountTransactionFirst($trans, $pmod_id)
    {
        return array_search($pmod_id, array_column($trans, 'pmod_id'));
    }

    // 정산이력 생성
    static public function addSettleHistory($trans, $virtual_account, $settle_amount, $terminal_amount, $cancel_deposit_amount)
    {
        // SettleHistoryMerchandise/Salesforce 정산이력 생성
        $total_settle_amount = $settle_amount - $terminal_amount;
        $date = date('Y-m-d');
        $data = [
            'brand_id'      => $trans['brand_id'],
            'settle_amount' => $total_settle_amount,
            'deposit_amount'=> $total_settle_amount,
            'settle_dt'     => $date,
            'deposit_dt'    => $date,
            'deposit_status'    => 5,
            'acct_name'         => $virtual_account['account_name'],
            'acct_num'          => $virtual_account['account_code'],
            'comm_settle_amount'=> $terminal_amount * -1,
        ];
        if(isset($trans['is_cancel']) === false)
        {
            $settle = [];
        }
        else if($trans['is_cancel'])
        {
            $settle = [
                'total_amount'  => $trans['amount'],
                'trx_amount'    => $trans['amount'] - $settle_amount,
                'cxl_amount'    => $trans['amount'],
                'cxl_count'     => 1,
            ];
        }
        else
        {
            $settle = [
                'total_amount'  => $trans['amount'],
                'trx_amount'    => $trans['amount'] - $settle_amount,
                'appr_amount'   => $trans['amount'],
                'appr_count'    => 1,
            ];
        }
        if($virtual_account['level'] === 10)
        {
            return SettleHistoryMerchandise::create(array_merge($data, $settle, [
                'mcht_id'       => $virtual_account['user_id'],
                'settle_fee'    => $trans['mcht_settle_fee'],
                'cancel_deposit_amount' => $cancel_deposit_amount,
            ]));
        }
        else
        {
            return SettleHistorySalesforce::create(array_merge($data, $settle, [
                'sales_id'       => $virtual_account['user_id'],
            ]));
        }
    }

    static public function addDepositHistory($trans, $virtual_account, $trans_amount, $settle_id, $deposit_type)
    {
        $data = [
            'trans_amount'  => $trans_amount,
            'trans_type'    => 0,
            'settle_id'     => $settle_id,
            'trx_id'        => $trans['trx_id'],
            'cxl_seq'       => $trans['cxl_seq'],
            'deposit_type'  => $deposit_type,
            'deposit_status'=> 1,
            'deposit_schedule_time' => date("Y-m-d H:i:s")
        ];
        $res = self::addHistory($virtual_account, $data);
        if($res->id)
        {
            $data['id'] = $res->id;
            self::updateVirtualAccount($virtual_account);
        }
        else
            $data['id'] = null;
        return $data;
    }

    static public function cancelDepositProcess($cancel_deposit, $va_id)
    {
        $virtual_account = self::getVirtualAccount($va_id);
        if($virtual_account)
        {
            $result = DB::transaction(function () use($cancel_deposit, $virtual_account) {
                $trans = [
                    'brand_id'  => $virtual_account['brand_id'],
                    'amount'    => 0,
                    'trx_id'    => null,
                    'cxl_seq'   => 0,
                    'mcht_settle_fee' => 0,
                ];
                # 2. 정산정보 추가
                $settle_res = self::addSettleHistory($trans, $virtual_account, 0, 0, $cancel_deposit->deposit_amount);
                if($settle_res)
                {
                    # 3. 입금정보 추가
                    $history = self::addDepositHistory($trans, $virtual_account, $cancel_deposit->deposit_amount, $settle_res->id, 3);
                    # 4. 취소수기입금 정산번호 매핑
                    $cancel_deposit->mcht_settle_id = $settle_res->id;
                    $cancel_deposit->save();
                    return true;
                }
                else
                {
                    error([], '정산이력 생성 실패');
                    throw new Exception('정산이력 생성 실패');
                    return false;
                }
            });
            return $result;
        }
        else
        {
            error([], '정산지갑이 존재하지 않음');
            return false;
        }
    }

}
