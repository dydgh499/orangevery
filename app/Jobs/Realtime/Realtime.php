<?php
namespace App\Jobs\Realtime;
use App\Http\Traits\Util\HttpTrait;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Option\Withdraw\SettleWithdrawValidate;
use App\Http\Controllers\Option\Withdraw\CMSTransactionValidate;
use App\Http\Controllers\Option\Withdraw\CMSTransactionBookValidate;

class Realtime
{
    use HttpTrait;
    public $db, $finance_van, $privacy, $deposit_type, $profit, $trsc_no, $withdraw_book_time;
    public function __construct($finance_van, $privacy, $deposit_type, $withdraw_book_time)
    {
        $this->db   = DB::connection('onequeue');
        $this->finance_van  = $finance_van;
        $this->privacy      = $privacy;
        $this->deposit_type = $deposit_type;
        $this->withdraw_book_time = $withdraw_book_time;

        $this->trsc_no  = 0;
        $this->profit   = 0;
    }

    public function settleWithdrawSequence($is_mcht, $settle, $timeout_codes)
    {
        $this->profit = $settle->settle_amount;
        $this->trsc_no = SettleWithdrawValidate::getTrxNum($is_mcht, $settle);
        return SettleWithdrawValidate::validate($settle, $is_mcht, $this->profit, $this->privacy['acct_bank_code'], $timeout_codes);
    }

    public function addSettleWithdraw($is_mcht, $settle, $code, $message)
    {
        return SettleWithdrawValidate::addWithdraw($this->finance_van, $is_mcht, $settle, $code, $message, $this->trsc_no);
    }

    public function updatedSettleWithDraw($is_mcht, $last_id, $code, $message)
    {
        return SettleWithdrawValidate::updateWithdraw($last_id, $is_mcht, $code, $message, $this->trsc_no);
    }

    public function updateSettleInfo($is_mcht, $settle)
    {
        return SettleWithdrawValidate::updateSettleInfo($is_mcht, $settle);
    }

    // 시퀀스
    public function CMSTransactionSequence($withdraw_amount)
    {
        $this->profit = $withdraw_amount;
        $this->trsc_no = $this->getCMSTransactionTrxNum();
        return CMSTransactionValidate::validate($this->privacy['acct_bank_code']);
    }
    
    // 거래번호 가져오기
    public function getCMSTransactionTrxNum()
    {
        return CMSTransactionValidate::getTrxNum($this->finance_van, $this->privacy, $this->profit, $this->withdraw_book_time);
    }

    // 지정계좌 이체 cms_transactions에 추가하기
    public function addCMSTransaction($code, $message, $note)
    {
        return CMSTransactionValidate::addWithdraw($this->finance_van, $code, $message, $note, 1, $this->profit, $this->trsc_no, $this->privacy);
    }

    // 지정계좌 이체 결과 cms_transactions의 result_code, message 업데이트
    public function updateCMSTransaction($last_id, $code, $message)
    {
        return CMSTransactionValidate::updateWithdraw($last_id, $code, $message);
    }

    public function getDepositName()
    {

        if(isset($this->finance_van['deposit_type']))
           return $this->finance_van['deposit_type'] ?  $this->privacy['acct_name'] : $this->finance_van['nick_name'];
        else
            return $this->finance_van['nick_name'];
    }

    public function getWithdrawStatus($json)
    {
        if($json['RESP_CD'] === "0000")
        {
            $code       = '0000';
            $message    = '이체 완료';
        }
        else
        {
            $code       = $json['RESP_CD'];
            $message    = $json['RESP_MSG'];
        }
        return [$code, $message];
    }
    /*
    // 지정계좌 이체 시퀀스
    public function CMSTransactionBookSequence($withdraw_amount)
    {
        $this->profit = $withdraw_amount;
        $this->trsc_no = $this->getCMSTransactionTrxNum();
        return CMSTransactionBookValidate::validate($this->privacy['acct_bank_code']);
    }

    // 지정계좌 이체 거래번호 가져오기
    public function getCMSTransactionBookTrxNum()
    {
        return CMSTransactionBookValidate::getTrxNum($this->finance_van['brand_id']);
    }

    // 지정계좌 이체 cms_transaction_book에 추가하기
    public function addCMSTransactionBook($code, $note)
    {
        return CMSTransactionBookValidate::addWithdraw($this->finance_van, $code, $note, 1, $this->profit, $this->trsc_no, $this->privacy);
    }
    */
    // 지정계좌 이체 결과 cms_transactions의 result_code, message 업데이트
    public function updateCMSTransactionBook($last_id, $code, $message)
    {
        return CMSTransactionValidate::updateWithdraw($last_id, $code, $message);
    }

    public function getWithdrawBookStatus($json)
    {
        if($json['RESP_CD'] === "0000")
        {
            $code       = '0000';
            $message    = '이체 완료';
        }
        else
        {
            $code       = $json['RESP_CD'];
            $message    = $json['RESP_MSG'];
        }
        return [$code, $message];
    }
}

?>
