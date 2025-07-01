<?php
namespace App\Http\Controllers\Option\Withdraw;

use App\Models\Service\CMSTransactionBooks;
use App\Models\Service\CMSTransaction;

use App\Models\Transaction;

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

    static public function cancelValidate($history)
    {
        return Transaction::where('ori_trx_id', $history['trx_id'])
            ->where('is_cancel', true)
            ->exists();
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
