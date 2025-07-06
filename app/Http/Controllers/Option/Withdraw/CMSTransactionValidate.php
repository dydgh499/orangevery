<?php
namespace App\Http\Controllers\Option\Withdraw;

use App\Models\Service\CMSTransactionBooks;
use App\Models\Service\CMSTransaction;

use App\Models\Transaction;

class CMSTransactionValidate
{
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
    
    static public function validate($acct_bank_code)
    {   // 은행정보 검증
        return self::acctBankCodeValidate($acct_bank_code);
    }
    
    static public function acctBankCodeValidate($acct_bank_code)
    {
        if($acct_bank_code === null)
            return ['PV485', '은행코드를 매칭할 수 없습니다.'];
        else
            return ['0000', ''];
    }
}
