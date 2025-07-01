<?php
namespace App\Http\Controllers\Option\Withdraw;

use App\Http\Controllers\Option\Withdraw\CMSTransactionLimitValidate;
use App\Models\Withdraws\VirtualAccount;
use App\Models\Withdraws\VirtualAccountHistory;
use App\Models\Withdraws\VirtualAccountWithdraw;
use App\Models\Service\CMSTransactionBooks;
use App\Models\Service\CMSTransaction;

use App\Models\Transaction;
use App\Enums\RealtimeDepositCode;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class CMSTransactionValidate
{
    // 출금이력 거래번호 생성
    static public function getTrxNum($finance_van, $privacy, $withdraw_amount, $withdraw_book_time)
    {
        $existing = CMSTransactionBooks::where('brand_id', $finance_van['brand_id'])
        ->where('fin_id', $finance_van['id'])
        ->where('acct_num', $privacy['acct_num'])
        ->where('withdraw_book_time', $withdraw_book_time)
        ->where('amount', $withdraw_amount)
        ->where('is_withdraw', 1)
        ->orderBy('id', 'desc') // 최신값 우선
        ->value('trans_seq_num');

        return $existing;
    }

    static public function getVirtualAccount($va_id)
    {
        $key_name = "virtual-account-".$va_id;
        $virtual_accounts = Redis::get($key_name);
        if($virtual_accounts)
            return json_decode($virtual_accounts, true);
        else
        {
            $virtual_accounts = VirtualAccount::where('id', $va_id)->first();
            if($virtual_accounts)
            {
                $virtual_accounts = json_decode(json_encode($virtual_accounts), true);
                Redis::set($key_name, json_encode($virtual_accounts), 'EX', 300);
            }
            return $virtual_accounts;
        }
    }

    // 정산지갑 데이터 생성
    static public function addHistory($virtual_account, $history)
    {
        return VirtualAccountHistory::create(array_merge([
            'brand_id'      => $virtual_account['brand_id'],
            'va_id'         => $virtual_account['id'],
            'level'         => $virtual_account['level'],
        ], $history));
    }

    // 정산지갑 데이터 업데이트
    static public function updateHistory($last_id, $withdraw_status, $trans_amount)
    {
        return VirtualAccountHistory::where('id', $last_id)->update([
            'withdraw_status'   => $withdraw_status,
            'trans_amount'      => $trans_amount,
        ]);
    }

    // 출금예약이력 데이터 업데이트 테스트
    static public function updateHistoryTest($last_id, $withdraw_status, $trans_amount)
    {
        return CMSTransactionBooks::where('id', $last_id)->update([
            'withdraw_status'   => $withdraw_status,
            'trans_amount'      => $trans_amount,
        ]);
    }

    static public function addWithdraw($finance_van, $code, $message, $note, $is_withdraw, $profit, $trsc_no, $privacy)
    {
        $profit = $is_withdraw ? $profit * -1 : $profit;
        $params = [
            'brand_id'  => $finance_van['brand_id'],
            'fin_id'    => $finance_van['id'],
            'result_code' => $code,
            'is_withdraw' => $is_withdraw,
            'trx_at'        => date("Y-m-d H:i:s"),
            'trans_seq_num' => $trsc_no,
            'amount'  => $profit,
            'message' => $message,
            'note' => $note,
        ];
        $params = array_merge($params, $privacy);
        $res = CMSTransaction::create($params);
        return $res->id;
    }

    static public function updateWithdraw($last_id, $result_code, $message)
    {
        $params = [
            'result_code'=> $result_code,
            'message' => $message,
        ];
        return CMSTransaction::where('id', $last_id)->update($params);
    }

    // 정산지갑 잔액 업데이트
    static public function updateVirtualAccount($virtual_account)
    {
        $result = self::getAbleBalance($virtual_account);
        VirtualAccount::where('id', $virtual_account['id'])->update(['balance' => $result['withdraw_able_amount']]);
    }

    // 잔액 검증
    static public function getAbleBalance($virtual_account)
    {
        $chart = VirtualAccountHistory::where('va_id', $virtual_account['id'])->first([
                DB::raw("SUM(IF(trans_type = 0 AND deposit_status = 1, trans_amount, 0)) AS deposit_success_amount"),
                DB::raw("SUM(IF(trans_type = 1 AND (withdraw_status = 1 OR withdraw_status = 0), trans_amount, 0)) AS withdraw_success_amount"),
            ]);
        if($chart)
        {
            $withdraw_able_amount = (int)$chart->deposit_success_amount + (int)$chart->withdraw_success_amount;
            return [
                'withdraw_able_amount'  => $withdraw_able_amount,
                'withdraw_fee'          => $virtual_account['withdraw_fee'],
            ];
        }
        else
        {
            return [
                'withdraw_able_amount'  => 0,
                'withdraw_fee'          => $virtual_account['withdraw_fee'],
            ];
        }
    }

    static public function cancelValidate($history)
    {
        return Transaction::where('ori_trx_id', $history['trx_id'])
            ->where('is_cancel', true)
            ->exists();
    }

    // 잔액검증
    static public function balanceValidate($virtual_account, $amount, $history)
    {
        $result = self::getAbleBalance($virtual_account);
        $withdraw_able_amount = $result['withdraw_able_amount'] - $history['withdraw_fee'];

        if($virtual_account['withdraw_type'] === 0)
        {
            if($withdraw_able_amount < $amount)
            {
                $message = '정산지갑 잔액이 부족합니다.(출금가능액: '.number_format($withdraw_able_amount).'원, 출금시도:  '.number_format($amount).'원)';
                return ['PV489', $message];
            }
            else
                return ['0000', ''];
        }
        else if($virtual_account['withdraw_type'] === 1)
        {
            /*
            $able_balance   = $result['withdraw_able_amount'] + $amount;
            if($able_balance < $amount)
            {
                $message = '정산지갑 잔액이 부족합니다.(출금가능액: '.number_format($able_balance).'원, 출금시도:  '.number_format($amount).'원)';
                return ['PV489', $message];
            }
            else
            */
                return ['0000', ''];
        }
        else
            return ['0000', ''];
    }

    // 즉시출금 전용검증
    static public function autoWithdrawValidate($virtual_account, $history)
    {
        if($virtual_account['withdraw_type'] === 1)
        {   //즉시
            if(self::cancelValidate($history))
                return ['PV484', '취소된 입금건으로 이체하지 않았습니다.'];
        }
        return ['0000', ''];
    }
}
