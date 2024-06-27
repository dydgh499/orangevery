<?php

namespace App\Http\Controllers\BeforeSystem;

use App\Http\Traits\StoresTrait;
use App\Http\Traits\BeforeSystem\BeforeSystemTrait;
use App\Http\Traits\Settle\TransactionTrait;

class Transaction
{
    use StoresTrait, BeforeSystemTrait, TransactionTrait;

    public $paywell, $payvery, $paywell_to_payvery, $current_time;
    protected $connect_pgs, $connect_pss, $connect_cls, $connect_finance;
    protected $connect_mchts, $connect_sales, $payvery_mods;
    protected $payvery_mchts, $payvery_sales, $payvery_finances;
    protected $use_realtime, $dev_settle_type;

    public function __construct($use_realtime, $dev_settle_type)
    {
        $this->paywell = [];
        $this->payvery = [];
        $this->paywell_to_payvery = [];
        $this->current_time = date('Y-m-d H:i:s');
        $this->use_realtime = $use_realtime;
        $this->dev_settle_type = $dev_settle_type;
    }

    public function connectPGInfo($connect_pgs, $connect_pss, $connect_cls)
    {
        $this->connect_pgs = $connect_pgs;
        $this->connect_pss = $connect_pss;
        $this->connect_cls = $connect_cls;
    }

    public function connectUsers($connect_mchts, $connect_sales, $payvery_mchts, $payvery_sales)
    {
        $this->connect_mchts = $connect_mchts;
        $this->connect_sales = $connect_sales;

        $this->payvery_mchts = json_decode(json_encode($payvery_mchts), true);
    }

    public function connectPmod($payvery_mods)
    {
        $this->payvery_mods = json_decode(json_encode($payvery_mods), true);
    }

    public function getId($items, $id, $default=0)
    {
        return isset($items[$id]) ? $items[$id] : 0;
    }

    public function getPaywell($paywell_table, $brand_id, $before_brand_id, $s_dt, $e_dt)
    {
        $items = [];
        $datas = $paywell_table
                ->where('DNS_PK', $before_brand_id)
                ->where('TRADE_DT', '>', $s_dt)
                ->where('TRADE_DT', '<=', $e_dt)
                ->orderby('PK', 'ASC')
                ->chunk(999, function($transactions) use(&$items, $brand_id) {
                    $payvery_mchts_ids = array_column($this->payvery_mchts, 'id');
                    #print('insert prepare items: '.count($items)."\r\n");
                    foreach ($transactions as $transaction) {
                        $mcht_id = $this->getId($this->connect_mchts, $transaction->USER_PK);
                        if(!$mcht_id)
                        {
                            $idx = array_search($transaction->MD_NM, array_column($this->payvery_mchts, 'mcht_name'));
                            if($idx !== false)
                            {
                                $mcht = $this->payvery_mchts[$idx];
                                $mcht_id = $this->payvery_mchts[$idx]['id'];
                            }
                            else
                                $mcht_id = -1;
                        }
                        if($mcht_id || $mcht_id == -1)
                        {
                            $mid = $transaction->MID;
                            $tid = $transaction->CAT_ID;

                            if($mcht_id != -1)
                            {
                                $pmod_idx = null;
                                foreach ($this->payvery_mods as $index => $item) {
                                    if ($item['mcht_id'] == $mcht_id && $item['mid'] == $mid && $item['tid'] == $tid) {
                                        $pmod_idx = $index;
                                        break;
                                    }
                                }
                                $pmod = ($pmod_idx !== null) ? $this->payvery_mods[$pmod_idx] : null;
    
                                $idx = array_search($mcht_id, $payvery_mchts_ids);
                                $mcht = $idx === false ? null : $this->payvery_mchts[$idx];
    
                                $custom_id = $mcht ? $mcht['custom_id'] : 0;
                                $pmod_id = $pmod ? $pmod['id'] : 0;
                                $module_type = $pmod ? $pmod['module_type'] : 0;
                                $terminal_id = $pmod ? $pmod['terminal_id'] : 0;
                                $settle_type = $pmod ? $pmod['settle_type'] : 0;    
                            }
                            else
                            {
                                $mcht_id = 1;
                                $custom_id = 0;
                                $pmod_id = -1;
                                $module_type = 0;
                                $terminal_id = 0;
                                $settle_type = 0;
                                #print("*** not found mcht by merchandise name - found:".$transaction->MD_NM." *** \n");
                            }

                            $sales4_id = $this->getId($this->connect_sales, $transaction->SLSFC_PK);
                            $sales3_id = $this->getId($this->connect_sales, $transaction->BRANCH_PK);
                            $sales2_id = $this->getId($this->connect_sales, $transaction->DIST_PK);
                            $sales1_id = $this->getId($this->connect_sales, $transaction->AGCY_PK);

                            $pg_id = $this->getId($this->connect_pgs, $transaction->PGID_PK);
                            $ps_id = $this->getId($this->connect_pss, $transaction->PG_SEC_PK);

                            $amount = $transaction->TRADE_PR;
                            $dpst_fee = $transaction->DPST_FEE;
                            if($transaction->IS_CANCEL)
                            {
                                $amount *= -1;
                                $dpst_fee *= -1;
                            }

                            $item = [
                                'brand_id' => $brand_id,
                                'mcht_id' => $mcht_id,
                                'brand_settle_amount' => 0,

                                'dev_realtime_fee' => $this->use_realtime ? 0.001 : 0,
                                'dev_realtime_settle_amount' => 0,
                                
                                'dev_fee' => $transaction->DEV_FEE,
                                'dev_settle_id' => null,
                                'dev_settle_amount' => 0,
                                
                                'sales5_id' => 0,
                                'sales5_fee' => 0,
                                'sales5_settle_id' => null,
                                'sales5_settle_amount' => 0,
                                'sales4_id' => $sales4_id,
                                'sales4_fee' => $transaction->SLSFC_FEE,
                                'sales4_settle_id' => null,
                                'sales4_settle_amount' => 0,                            
                                'sales3_id' => $sales3_id,
                                'sales3_fee' => $transaction->BRANCH_FEE,
                                'sales3_settle_id' => null,
                                'sales3_settle_amount' => 0,
                                'sales2_id' => $sales2_id,
                                'sales2_fee' => $transaction->DIST_FEE,
                                'sales2_settle_id' => null,
                                'sales2_settle_amount' => 0,
                                'sales1_id' => $sales1_id,
                                'sales1_fee' => $transaction->AGCY_FEE,
                                'sales1_settle_id' => null,
                                'sales1_settle_amount' => 0,
                                'sales0_id' => 0,
                                'sales0_fee' => 0,
                                'sales0_settle_id' => null,
                                'sales0_settle_amount' => 0,
                                //
                                'pg_id' => $pg_id,
                                'ps_id' => $ps_id,
                                'ps_fee' => $transaction->PG_FEE,
                                'pmod_id' => $pmod_id,
                                'custom_id' => $custom_id,
                                'terminal_id' => $terminal_id,
                                'mcht_fee' => $transaction->MD_FEE,
                                'hold_fee' => $transaction->HOLD_AMT_FEE,
                                'pg_settle_type' => 1,      //주말포함
                                'mcht_settle_type' => $settle_type,    //D+1 통일
                                'mcht_settle_fee' => $dpst_fee,
                                'mcht_settle_id' => null,
                                'mcht_settle_amount' => 0,
                                
                                'trx_dt' => $transaction->TRADE_DT,
                                'trx_tm' => $transaction->TRADE_TM,
                                'cxl_dt' => $transaction->CXL_DT,
                                'cxl_tm' => $transaction->CXL_TM,
                                'cxl_seq' => $transaction->IS_CANCEL ? 1 : 0,
                                'is_cancel' => $transaction->IS_CANCEL,
                                'amount' => $amount,
                                'module_type' => $module_type,
                                
                                'ord_num' => $transaction->ORDER_NM,
                                'mid' => $transaction->MID,
                                'tid' => $transaction->CAT_ID,
                                'trx_id' => $transaction->TID_NM,
                                'ori_trx_id' => $transaction->IS_CANCEL ? $transaction->TID_NM : null,
    
                                'card_num' => $transaction->CARD_NUM,
                                'issuer' => $transaction->CARD_NM,
                                'acquirer' => $transaction->BANK_NM,
                                'appr_num' => $transaction->APPR_NUM,
    
                                'installment' => $transaction->INSTALLMENT ? $transaction->INSTALLMENT : 0,
                                'buyer_name' => $transaction->BUYER_NM,
                                'buyer_phone' => $transaction->BUYER_PHONE,
                                'item_name' => $transaction->ITEM_NM,
    
                                'PK' => $transaction->PK,
                                'created_at' => $this->current_time,
                                'updated_at' => $this->current_time,
                            ];
                            $item['settle_dt'] = $this->getSettleDate($item['is_cancel'] ? $item['cxl_dt'] : $item['trx_dt'], $item['mcht_settle_type']+1, 1, '');
                            $item['trx_at'] = $item['is_cancel'] ? ($item['cxl_dt']." ".$item['cxl_tm']) : ($item['trx_dt']." ".$item['trx_tm']);
                            if(strpos(date($item['trx_at']), '1970-01-01 09:00:00') === false)
                                $items[] = $item;                            
                        }
                    }
                });
        print('set settle amount prepare items: '.count($items)."\r\n");
        $items = json_decode(json_encode($items), true);
        $items = $this->setSettleAmount($items, $this->dev_settle_type);
        $this->paywell = $items;
        print("complate transactions getPaywell - found:".count($this->paywell)."\r\n");
    }

    public function setPayvery($payvery_table, $brand_id, $s_dt, $e_dt)
    {
        $tran_ids = $payvery_table
            ->where('brand_id', $brand_id)
            ->where('trx_dt', '>', $s_dt." 00:00:00")
            ->where('trx_dt', '<=', $e_dt." 23:59:59")
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
        print("complate transactions array_filter - filtered:".count($this->paywell)."\r\n");
        $items = $this->getPayveryFormat($this->paywell);
        $res   = $this->manyInsert($payvery_table, $items);
        if($res)
        {
            $this->payvery = $this->getPayvery($payvery_table, $brand_id, $this->current_time);
            $this->paywell_to_payvery = $this->connect($this->payvery, $this->paywell);
        }
    }
}
