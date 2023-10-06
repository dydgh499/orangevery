<?php

namespace App\Http\Controllers\BeforeSystem;

use App\Http\Traits\StoresTrait;
use App\Http\Traits\BeforeSystem\BeforeSystemTrait;

class RealtimeSendHistory
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

    public function connectUsers($connect_mchts, $connect_trans, $payvery_mchts)
    {
        $this->connect_mchts = $connect_mchts;
        $this->connect_trans = $connect_trans;
        $this->payvery_mchts = json_decode(json_encode($payvery_mchts), true);
    }

    public function getPaywell($paywell_table, $brand_id, $before_brand_id)
    {
        $items = [];
        $logs = $paywell_table
                ->join('deposit', 'realtime_trans_log.DPST_PK', '=', 'deposit.PK')
                ->where('deposit.DNS_PK', $before_brand_id)
                ->orderby('realtime_trans_log.PK', 'DESC')
                ->get(['realtime_trans_log.*']);
        foreach($logs as $log) {
            $item = [
                'brand_id' => $brand_id,
                'trans_id' => $this->connect_trans[$log->DPST_PK],
                'mcht_id'  => $this->connect_mchts[$log->USER_PK],
                'request_type' => $log->RT_TYPE,

                'finance_id' => 7,
                'result_code' => $log->CODE,
                'message' => $log->MSG,
                'amount' => $log->AMOUNT,
                
                'acct_num' => $log->RCV_ACCT_NO,
                'acct_bank_name' => $log->RCV_ACCT_NM,
                'acct_bank_code' => $log->RCV_ACCT_CD,
                'trans_seq_num' => $log->TRSC_SEQ_NO,

                'PK' => $log->PK,
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
