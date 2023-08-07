<?php

namespace App\Http\Controllers\BeforeSystem;

use App\Http\Traits\StoresTrait;
use App\Http\Traits\BeforeSystem\BeforeSystemTrait;

class PaymentSection
{
    use StoresTrait, BeforeSystemTrait;

    public $paywell, $payvery, $paywell_to_payvery, $current_time;
    public function __construct($payvery_pgs, $paywell_to_payvery_pgs)
    {
        $this->payvery_pgs = $payvery_pgs;
        $this->paywell_to_payvery_pgs = $paywell_to_payvery_pgs;
     
        $this->paywell = [];
        $this->payvery = [];
        $this->paywell_to_payvery = [];
        $this->current_time = date('Y-m-d H:i:s');
    }

    public function getPaywell($paywell_table, $brand_id, $before_brand_id)
    {
        $items = [];
        $pss = $paywell_table
                ->where('DNS_PK', $before_brand_id)
                ->where('ITEM_TYPE', -1)
                ->orderby('PK', 'DESC')
                ->get();
        foreach($pss as $ps) {
            if(isset($this->paywell_to_payvery_pgs[$ps->PG_PK]) == false)
                continue;
            $item = [
                'brand_id' => $brand_id,
                'trx_fee'=> $ps->ITEM_COST,
                'name' => $ps->ITEM_NM,
                'pg_id'=> $this->paywell_to_payvery_pgs[$ps->PG_PK],
                'PK' => $ps->PK,
                'is_delete' => !$ps->IS_USE,
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
