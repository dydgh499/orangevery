<?php

namespace App\Jobs\Realtime\Hecto;
use App\Jobs\Realtime\Hecto\Hecto;

class HectoWrap extends Hecto
{
    public $trx_dt, $trx_tm;
    public function __construct($finance_van, $privacy, $deposit_type, $withdraw_book_time)
    {
        parent::__construct($finance_van, $privacy, $deposit_type, $withdraw_book_time);
        if(isset($this->privacy['acct_bank_name']))
        {
            $this->privacy['acct_bank_code'] = $this->getBankCode($this->privacy['acct_bank_name']);
            $this->privacy['acct_num']  = str_replace('-', '', $this->privacy['acct_num']);
            $this->privacy['acct_num']  = str_replace(' ', '', $this->privacy['acct_num']);
        }
        if($deposit_type !== 0)
        {
            $this->trx_dt = date('Ymd');
            $this->trx_tm = date('His');
        }
        else
        {
            $this->trx_dt = '';
            $this->trx_tm = '';
        }
    }

    public function getBalance()
    {
        $res = $this->send("/pyag/v1/fxBalance", ['mchtId' => $this->finance_van['corp_code']]);
        if($res['RESP_CD'] === "0000" && $res['outStatCd'] === "0021")
            $res['WDRW_CAN_AMT'] = $res['blcKrw'];
        return $res;
    }

    public function deposit()
    {
        $trx_dt = str_replace('-', '', $this->trx_dt);
        $trx_tm = str_replace(':', '', $this->trx_tm);
        $params = [
            'mchtId' => $this->finance_van['corp_code'],
            'mchtTrdNo' => $this->trsc_no,
            'encCd' => 23,
            'trdDt' => $trx_dt,
            'trdTm' => $trx_tm,
            'bankCd' => $this->privacy['acct_bank_code'],
            'custAcntNo' => $this->privacy['acct_num'],
            'custAcntSumry' => mb_substr($this->getDepositName(), 0, 10),
            'amt' => $this->profit,
        ];
        $params['custAcntNo'] = base64_encode(openssl_encrypt($params['custAcntNo'], "AES-256-ECB",  $this->finance_van['enc_key'] , OPENSSL_RAW_DATA));
        $params['amt'] = base64_encode(openssl_encrypt($params['amt'], "AES-256-ECB",  $this->finance_van['enc_key'] , OPENSSL_RAW_DATA));

        $res = $this->send("/pyag/v1/fxTransKrw", $params);
        return $res;
    }

    public function getDepositResult($ori_trx_id)
    {
        $trx_dt = str_replace('-', '', $this->trx_dt);
        $params = [
            'mchtId'    => $this->finance_van['corp_code'],
            'mchtTrdNo' => $ori_trx_id,
            'trdNo'     => $this->trsc_no,
            'orgTrdDt'  => $trx_dt,
        ];
        $res = $this->send("/pyag/v1/fxResult", $params);
        return $res;
    }
}
?>
