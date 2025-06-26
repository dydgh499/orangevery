<?php

namespace App\Jobs\Realtime;

use App\Jobs\Realtime\Realtime;
use App\Models\Service\FinanceVan;
use App\Models\Operator;
use Illuminate\Support\Facades\Redis;
use App\Http\Controllers\Ablilty\SMS;
// 실시간 이상거래 탐지
class RealtimeFDS extends Realtime
{
    public function __construct($finance_van, $privacy, $deposit_type, $withdraw_book_time)
    {
        parent::__construct($finance_van, $privacy, $deposit_type, $withdraw_book_time);
    }

    public function setFinanceStatus($status)
    {
        return FinanceVan::where('id', $this->finance_van['id'])->update(['balance_status' => $status]);
    }

    public function getNoticeActiveOperators()
    {
        $key_name = 'is-notice-realtime-warning-operators:'.$this->finance_van['brand_id'];
        $operators = Redis::get($key_name);
        if($operators === null)
        {
            $db_operators = Operator::where('brand_id', $this->finance_van['brand_id'])
            	->where('is_notice_realtime_warning', 1)
            	->where('is_active', 1)
            	->pluck('phone_num')->all();
            if($db_operators)
            {
                Redis::set($key_name, json_encode($db_operators), 'EX', 300);
                $operators = json_encode($db_operators);
            }
            else
                $operators = json_encode([]);
        }
        return json_decode($operators, true);
    }

    public function balanceValidate($can_balance)
    {
        $min_balance_limit = (int)$this->finance_van['min_balance_limit'] * 10000;
        if((int)$can_balance < $min_balance_limit)
        {    // 잔액상태 부족
            if($this->finance_van['balance_status'])  // 상태 값이 충분?
            {   //부족 상태로 변경
                if($this->setFinanceStatus(0))
                {   //메세지 전송
                    $this->finance_van['balance_status'] = 0;
                    $this->sendSMS("[".$this->finance_van['nick_name']."] 실시간 이체 잔액이 부족합니다.\n(현재: ".number_format($can_balance)."원)");
                    return false;
                }
            }
        }
        else
        {
            if($this->finance_van['balance_status'] === false)
            {   // 잔액은 충분한데 상태값이 안바뀐 상태
                if($this->setFinanceStatus(1))
                    $this->finance_van['balance_status'] = 1;
            }
        }
        return true;
    }

    public function sendSMS($message)
    {
        $operators = $this->getNoticeActiveOperators();
        if(count($operators))
        {
            for ($i=0; $i < count($operators); $i++)
            {
                SMS::send($operators[$i], $message, $this->finance_van['brand_id']);
            }
            SMS::validate($this->finance_van['brand_id']);
        }
    }

    public function sendDangerMessage($history, $error_code, $error_message)
    {
        if(env('APP_ENV') !== 'local')
        {
            $message = "[".$this->finance_van['nick_name']."] 실시간 이체 모듈이 통신 과정중 이상사항을 발견하였습니다.\n".
                "\n거래번호:".$history['trx_id'].
                "\n시도금액:".number_format($this->profit).'원'.
                "\n에러코드:".$error_code.
                "\n내용:".$error_message;
            $this->sendSMS($message);
        }
    }
}
