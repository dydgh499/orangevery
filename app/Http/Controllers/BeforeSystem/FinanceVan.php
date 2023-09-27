<?php

namespace App\Http\Controllers\BeforeSystem;

use App\Http\Traits\StoresTrait;
use App\Http\Traits\BeforeSystem\BeforeSystemTrait;

class FinanceVan
{
    use StoresTrait, BeforeSystemTrait;

    public $paywell, $payvery, $paywell_to_payvery, $current_time;
    public function __construct()
    {
        $this->paywell = [];
        $this->payvery = [];
        $this->paywell_to_payvery = [];
        $this->current_time = date('Y-m-d H:i:s');
    }

    public function getPaywell($paywell_table, $brand_id, $before_brand_id)
    {
        $items = [];
        $finances = $paywell_table
                ->where('DNS_PK', $before_brand_id)
                ->orderby('PK', 'DESC')
                ->get();
        foreach($finances as $finance) {
            $item = [
                'brand_id' => $brand_id,
                'fin_type' => 1,
                'balance_status' => $finance->BAL_STATUS,
                'dev_fee' => $finance->DEV_FEE,
                'api_key' => $finance->API_KEY,
                'sms_key' => $finance->SMS_KEY,
                'sms_id' => $finance->SMS_ID,
                'sms_sender_phone' => $finance->SMS_SENDER,
                'sms_receive_phone' => $finance->REP_PHONE,
                'min_balance_limit' => $finance->MIN_AMOUNT/10000,
                'corp_code' => $finance->CORP_CD,
                'corp_name' => $finance->CORP_NM,
                'bank_code' => $finance->BANK_CD,
                'nick_name' => $finance->NICK_NM,
                'withdraw_acct_num' => $finance->WDRW_ACCT_NO,
                'finance_company_num' => 1,
                'PK' => $finance->PK,
                'created_at' => $this->current_time,
                'updated_at' => $this->current_time,
            ];
            array_push($items, $item);
        }
        $this->paywell = $items;
    }

    public function setPayvery($payvery_table, $brand_id)
    {
        $items = $this->getPayveryFormat($this->paywell);
        $res   = $this->manyInsert($payvery_table, $items);
        if($res)
        {
            $this->payvery = $this->getPayvery($payvery_table, $brand_id, $this->current_time);
            $this->paywell_to_payvery = $this->connect($this->payvery, $this->paywell);
        }
    }
}
