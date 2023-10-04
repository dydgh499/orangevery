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
                            'mcht_id' => $this->paywell_to_payvery_mchts[$transaction->USER_PK],
                            'brand_settle_amount' => 0,
                            'dev_realtime_fee' => 0,
                            'dev_realtime_settle_amount' => 0,
                            
                            'dev_fee' => $transaction->DEV_FEE,
                            'dev_settle_id' => -1,
                            'dev_settle_amount' => 0,
                            
                            'sales5_id' => 0,
                            'sales5_fee' => 0,
                            'sales5_settle_id' => -1,
                            'sales5_settle_amount' => 0,
                            'sales4_id' => $this->paywell_to_payvery_sales[$transaction->SLSFC_PK],
                            'sales4_fee' => $transaction->SLSFC_FEE,
                            'sales4_settle_id' => -1,
                            'sales4_settle_amount' => 0,                            
                            'sales3_id' => $this->paywell_to_payvery_sales[$transaction->BRANCH_PK],
                            'sales3_fee' => $transaction->BRANCH_FEE,
                            'sales3_settle_id' => -1,
                            'sales3_settle_amount' => 0,
                            'sales2_id' => $this->paywell_to_payvery_sales[$transaction->DIST_PK],
                            'sales2_fee' => $transaction->DIST_FEE,
                            'sales2_settle_id' => -1,
                            'sales2_settle_amount' => 0,
                            'sales1_id' => $this->paywell_to_payvery_sales[$transaction->AGCY_PK],
                            'sales1_fee' => $transaction->AGCY_FEE,
                            'sales1_settle_id' => -1,
                            'sales1_settle_amount' => 0,
                            'sales0_id' => 0,
                            'sales0_fee' => 0,
                            'sales0_settle_id' => -1,
                            'sales0_settle_amount' => 0,
                            //
                            'pg_id' => $this->paywell_to_payvery_pgs[$transaction->PGID_PK],
                            'ps_id' => $this->paywell_to_payvery_pss[$transaction->PGID_PK],
                            'ps_fee' => 0,
                            'pmod_id' => 0,
                            'custom_id' => $this->paywell_to_payvery_cls[$transaction->CST_FL],
                            'terminal_id' => $this->paywell_to_payvery_cls[$transaction->WD_TYPE],
                            'mcht_fee' => $transaction->MD_FEE,
                            'hold_fee' => $transaction->HOLD_AMT_FEE,
                            'pg_settle_type' => 0,      //주말포함
                            'mcht_settle_type' => 0,    //D+1 통일
                            'mcht_settle_fee' => 0,
                            'mcht_settle_id' => 0,
                            'mcht_settle_amount' => 0,
                            
                            'trx_dt' => $transaction->TRADE_DT,
                            'trx_tm' => $transaction->TRADE_TM,
                            'cxl_dt' => $transaction->CXL_DT,
                            'cxl_tm' => $transaction->CXL_TM,
                            'is_cancel' => $transaction->IS_CANCEL,
                            'amount' => $transaction->TRADE_PR,
                            'module_type' => 0,
                            
                            'ord_num' => $transaction->ORDER_NM,
                            'mid' => $transaction->MID,
                            'tid' => $transaction->CAT_ID,
                            'trx_id' => $transaction->TID_NM,
                            'ori_trx_id' => null,

                            'card_num' => $transaction->CARD_NUM,
                            'issuer' => $transaction->CARD_NM,
                            'acquirer' => $transaction->BANK_NM,
                            'appr_num' => $transaction->APPR_NUM,

                            'installment' => $transaction->INSTALLMENT,
                            'buyer_name' => $transaction->BUYER_NM,
                            'buyer_phone' => $transaction->BUYER_PHONE,
                            'item_name' => $transaction->ITEM_NM,

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
