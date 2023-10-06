<?php

namespace App\Http\Controllers\BeforeSystem;

use App\Http\Traits\StoresTrait;
use App\Http\Traits\BeforeSystem\BeforeSystemTrait;
use App\Http\Traits\Settle\TransactionTrait;

class Transaction
{
    use StoresTrait, BeforeSystemTrait, TransactionTrait;

    public $paywell, $payvery, $connect, $current_time;
    protected $connect_pgs, $connect_pss, $connect_cls, $connect_finance;
    protected $connect_mchts, $connect_sales, $payvery_mods;
    protected $payvery_mchts, $payvery_sales;
    
    public function __construct()
    {
        $this->paywell = [];
        $this->payvery = [];
        $this->connect = [];
        $this->current_time = date('Y-m-d H:i:s');
    }

    public function connectPGInfo($connect_pgs, $connect_pss, $connect_cls, $connect_finance)
    {
        $this->connect_pgs = $connect_pgs;
        $this->connect_pss = $connect_pss;
        $this->connect_cls = $connect_cls;
        $this->connect_finance = $connect_finance;
    }

    public function connectUsers($connect_mchts, $connect_sales, $payvery_mchts, $payvery_sales)
    {
        $this->connect_mchts = $connect_mchts;
        $this->connect_sales = $connect_sales;

        $this->payvery_mchts = collect($payvery_mchts);
        $this->payvery_sales = collect($payvery_sales);
    }

    public function connectPmod($payvery_mods)
    {
        $this->payvery_mods = collect($payvery_mods);
    }

    public function getId($items, $id, $default=0)
    {
        return isset($items[$id]) ? $items[$id] : 0;
    }

    public function getPaywell($paywell_table, $brand_id, $before_brand_id)
    {
        $items = [];
        $datas = $paywell_table
                ->where('DNS_PK', $before_brand_id)
                ->orderby('PK', 'ASC')
                ->chunk(999, function($transactions) use(&$items, $brand_id) {
                    foreach ($transactions as $transaction) {
                        $mcht_id = $this->getId($this->connect_mchts, $transaction->USER_PK);
                        if($mcht_id)
                        {
                            $mid = $transaction->MID;
                            $tid = $transaction->CAT_ID;
                            $pmod = $this->payvery_mods->first(function($item) use($mcht_id, $mid, $tid) { 
                                return $item->mcht_id == $mcht_id && $item->mid == $mid && $item->tid == $tid; 
                            });
                            $pmod_id = $pmod ? $pmod->id : 0;
                            $module_type = $pmod ? $pmod->module_type : 0;
                            $sales4_id = $this->getId($this->connect_sales, $transaction->SLSFC_PK);
                            $sales3_id = $this->getId($this->connect_sales, $transaction->BRANCH_PK);
                            $sales2_id = $this->getId($this->connect_sales, $transaction->DIST_PK);
                            $sales1_id = $this->getId($this->connect_sales, $transaction->AGCY_PK);

                            $pg_id = $this->getId($this->connect_pgs, $transaction->PGID_PK);
                            $ps_id = $this->getId($this->connect_pss, $transaction->PG_SEC_PK);

                            $cst_ft_id   = $this->getId($this->connect_cls, $transaction->CST_FL);
                            $terminal_id = $this->getId($this->connect_cls, $transaction->WD_TYPE);
                            $items[] = [
                                'brand_id' => $brand_id,
                                'mcht_id' => $mcht_id,
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
                                'sales4_id' => $sales4_id,
                                'sales4_fee' => $transaction->SLSFC_FEE,
                                'sales4_settle_id' => -1,
                                'sales4_settle_amount' => 0,                            
                                'sales3_id' => $sales3_id,
                                'sales3_fee' => $transaction->BRANCH_FEE,
                                'sales3_settle_id' => -1,
                                'sales3_settle_amount' => 0,
                                'sales2_id' => $sales2_id,
                                'sales2_fee' => $transaction->DIST_FEE,
                                'sales2_settle_id' => -1,
                                'sales2_settle_amount' => 0,
                                'sales1_id' => $sales1_id,
                                'sales1_fee' => $transaction->AGCY_FEE,
                                'sales1_settle_id' => -1,
                                'sales1_settle_amount' => 0,
                                'sales0_id' => 0,
                                'sales0_fee' => 0,
                                'sales0_settle_id' => -1,
                                'sales0_settle_amount' => 0,
                                //
                                'pg_id' => $pg_id,
                                'ps_id' => $ps_id,
                                'ps_fee' => $transaction->PG_FEE,
                                'pmod_id' => $pmod_id,
                                'custom_id' => $cst_ft_id,
                                'terminal_id' => $terminal_id,
                                'mcht_fee' => $transaction->MD_FEE,
                                'hold_fee' => $transaction->HOLD_AMT_FEE,
                                'pg_settle_type' => 0,      //주말포함
                                'mcht_settle_type' => 0,    //D+1 통일
                                'mcht_settle_fee' => $transaction->MD_FEE,
                                'mcht_settle_id' => -1,
                                'mcht_settle_amount' => 0,
                                
                                'trx_dt' => $transaction->TRADE_DT,
                                'trx_tm' => $transaction->TRADE_TM,
                                'cxl_dt' => $transaction->CXL_DT,
                                'cxl_tm' => $transaction->CXL_TM,
                                'is_cancel' => $transaction->IS_CANCEL,
                                'amount' => $transaction->TRADE_PR,
                                'module_type' => $module_type,
                                
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
                    }
                });
        $items = json_decode(json_encode($items), true);
        $items = $this->setSettleAmount($items, 0);
        $this->paywell = $items;
        print("complate transactions getPaywell - found:".count($this->paywell)."\n");
    }

    public function setPayvery($payvery_table, $brand_id)
    {
        $tran_ids = $payvery_table
            ->where('brand_id', $brand_id)
            ->pluck('trx_id')
            ->toArray();
        $datas = [];
        foreach($this->paywell as $paywell)
        {
            $idx = array_search($paywell['trx_id'], $tran_ids);
            if($idx === false)
                $datas[] = $paywell;
        }
        $this->paywell = $datas;
        print("complate transactions array_filter - filtered:".count($this->paywell)."\n");
        $items = $this->getPayveryFormat($this->paywell);
        $res   = $this->manyInsert($payvery_table, $items);
        if($res)
        {
            $this->payvery = $this->getPayvery($payvery_table, $brand_id, $this->current_time);
            $this->connect = $this->connect($this->payvery, $this->paywell);
        }
    }
}
