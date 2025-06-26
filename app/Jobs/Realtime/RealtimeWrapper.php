<?php
namespace App\Jobs\Realtime;
use App\Http\Controllers\Utils\FinanceVanUtil;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Option\Withdraw\Notification\Notification;
use App\Http\Controllers\Option\Withdraw\CMSTransactionValidate;

class RealtimeWrapper
{
    public $finance_van, $service, $withdraw_book_time;
    public function __construct($finance_van, $privacy, $deposit_type, $withdraw_book_time)
    {
        $this->finance_van = $finance_van;
        $this->withdraw_book_time = $withdraw_book_time;
        $this->service = FinanceVanUtil::getFinanceVanModule($finance_van, $privacy, $deposit_type, $withdraw_book_time);
    }
/*
    public function preTreatment()
    {
        if($this->finance_van['finance_company_num'] === 2)
        {   // hecto
            $this->service->trx_dt = date('Y-m-d');
            $this->service->trx_tm = date('H:i:s');
        }
        else if($this->finance_van['finance_company_num'] === 3)
        {   // welcome
            $this->service->trsc_no = null;
        }
        else if($this->finance_van['finance_company_num'] === 4)
        {   // dozn
            $this->service->trsc_no = $this->service->getWithdrawTrxNum();
        }
        else if($this->finance_van['finance_company_num'] === 5)
        {   // hyphen
            $this->service->trsc_no = $this->service->getWithdrawTrxNum();
        }
    }
*/
    public function afterTreatment($json)
    {
        if($json['RESP_CD'] === "0000")
        {
            if($this->finance_van['finance_company_num'] === 3)
            {
                $this->service->trsc_no = $json['tid'];
                $json['RESP_CD'] = "0050";
                $json['RESP_MSG'] = "이체 처리중";
            }
        }
        return $json;
    }

    public function deposit()
    {
        $json = $this->service->getBalance();
        if($json['RESP_CD'] === "0000")
        {
            $this->service->balanceValidate($json['WDRW_CAN_AMT']);
            return $this->service->deposit();
        }
        else
            return $json;
    }

    # 정산 이체
    public function settleDeposit($settle, $is_mcht)
    {
        [$code, $message, $data] = $this->service->settleWithdrawSequence($is_mcht, $settle, $this->service->timeout_codes);
        if($code === '0000')
        {
            $last_id = $this->service->addSettleWithdraw($is_mcht, $settle, '0050', '이체 처리중');
            $json = $this->service->deposit();
            $json = $this->afterTreatment($json);
            if($json['RESP_CD'] === "0000")
            {
                $this->service->updatedSettleWithDraw($is_mcht, $last_id, $json['RESP_CD'], $json['RESP_MSG']);
                $this->service->updateSettleInfo($is_mcht, $settle);
            }
        }
        else if($code === 'PV482')
        {
            $json = $this->service->getDepositResult($data);
            $this->service->addSettleWithdraw($is_mcht, $settle, $json['RESP_CD'], $json['RESP_MSG']);
        }
        else
        {
            $json = [
                'RESP_CD' => $code,
                'RESP_MSG' => $message
            ];
            $this->service->addSettleWithDraw($is_mcht, $settle, $code, $message);
        }
        return $json;
    }

    # 본사지정계좌 이체
    public function operateWithdraw($withdraw_amount, $note)
    {
        [$code, $message] = $this->service->CMSTransactionSequence($withdraw_amount); // trx_num 가져오고 은행코드 맞는지 확인
        if($code === '0000')
        {
            $last_id = $this->service->addCMSTransaction('0050', '이체 처리중', $note, 1); // cms_transactions 테이블에 요청한 값들 저장
            $json = $this->deposit(); // finance_van의 잔액 확인해서 잔액이 충분하면 5 부족하면 0
            $json = $this->afterTreatment($json); // 결과코드 deposit에서 나온 응답코드로 변경
            [$result_code, $message] = $this->service->getWithdrawStatus($json); // 결과코드 0000 이면 이체완료 메세지 응답 아니면 아닌 결과 응답
            $this->service->updateCMSTransaction($last_id, $result_code, $message); // 바로 위의 출금시도 응답코드, 결과 메세지 업데이트
        }
        else
        {
            $json = ['RESP_MSG' => $message, "RESP_CD"=> $code];
            $last_id = $this->service->addCMSTransaction($code, $message, $note, 1); // cms_transactions 테이블에 요청한 값들 저장
        }
        return $json;
    }
}
