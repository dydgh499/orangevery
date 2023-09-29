<?php

namespace App\Http\Controllers\BeforeSystem;

use App\Http\Traits\StoresTrait;
use App\Http\Traits\BeforeSystem\BeforeSystemTrait;

class Transactions
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

    public function connectPGInfo($paywell_to_payvery_pgs, $paywell_to_payvery_pss, $paywell_to_payvery_cls, $paywell_to_payvery_finance)
    {
        $this->paywell_to_payvery_pgs = $paywell_to_payvery_pgs;
        $this->paywell_to_payvery_pss = $paywell_to_payvery_pss;
        $this->paywell_to_payvery_cls = $paywell_to_payvery_cls;
        $this->paywell_to_payvery_finance = $paywell_to_payvery_finance;
    }

    public function connectUsers($paywell_to_payvery_mchts, $paywell_to_payvery_sales)
    {
        $this->paywell_to_payvery_mchts = $paywell_to_payvery_mchts;
        $this->paywell_to_payvery_sales = $paywell_to_payvery_sales;
    }

    public function connectPmod($paywell_to_payvery_pmod)
    {
        $this->paywell_to_payvery_pmod = $paywell_to_payvery_pmod;
    }


    public function getPaywell($paywell_table, $brand_id, $before_brand_id)
    {
        $items = [];
        $finances = $paywell_table
                ->where('DNS_PK', $before_brand_id)
                ->orderby('PK', 'DESC')
                ->chunk(999, function($transactions) use($items, $brand_id) {
                    foreach ($transactions as $transaction) {
                        $items[] = [
                            'brand_id' => $brand_id,
                            'mcht_id' => 0,
                            'brand_settle_amount' => 0,
                            'dev_realtime_fee' => 0,
                            'dev_realtime_settle_amount' => 0,
                            
                            'dev_fee' => 0,
                            'dev_settle_id' => 0,
                            'dev_settle_amount' => 0,
                            
                            'sales5_id' => 0,
                            'sales5_fee' => 0,
                            'sales5_settle_id' => 0,
                            'sales5_settle_amount' => 0,
                            'sales4_id' => 0,
                            'sales4_fee' => 0,
                            'sales4_settle_id' => 0,
                            'sales4_settle_amount' => 0,                            
                            'sales3_id' => 0,
                            'sales3_fee' => 0,
                            'sales3_settle_id' => 0,
                            'sales3_settle_amount' => 0,
                            'sales2_id' => 0,
                            'sales2_fee' => 0,
                            'sales2_settle_id' => 0,
                            'sales2_settle_amount' => 0,
                            'sales1_id' => 0,
                            'sales1_fee' => 0,
                            'sales1_settle_id' => 0,
                            'sales1_settle_amount' => 0,
                            'sales0_id' => 0,
                            'sales0_fee' => 0,
                            'sales0_settle_id' => 0,
                            'sales0_settle_amount' => 0,
                            //
                            'pg_id' => 0,
                            'ps_id' => 0,
                            'ps_fee' => 0,
                            'pmod_id' => 0,
                            'custom_id' => 0,
                            'terminal_id' => 0,
                            'mcht_fee' => 0,
                            'hold_fee' => 0,
                            'pg_settle_type' => 0,
                            'mcht_settle_type' => 0,
                            'mcht_settle_fee' => 0,
                            'mcht_settle_id' => 0,
                            'mcht_settle_amount' => 0,
                            
                            'trx_dt' => 0,
                            'trx_tm' => 0,
                            'cxl_dt' => 0,
                            'cxl_tm' => 0,
                            'is_cancel' => 0,
                            'amount' => 0,
                            'module_type' => 0,
                            
                            'module_type' => 0,
                            'ord_num' => 0,
                            'mid' => 0,
                            'tid' => 0,
                            'trx_id' => 0,
                            'ori_trx_id' => 0,

                            'card_num' => 0,
                            'issuer' => 0,
                            'acquirer' => 0,
                            'appr_num' => 0,

                            'installment' => 0,
                            'buyer_name' => 0,
                            'buyer_phone' => 0,
                            'item_name' => 0,

                            'PK' => $transaction->PK,
                            'created_at' => $this->current_time,
                            'updated_at' => $this->current_time,
                        ];
                    }
                });
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
