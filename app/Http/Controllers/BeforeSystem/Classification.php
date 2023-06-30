<?php

namespace App\Http\Controllers\BeforeSystem;

use App\Http\Traits\StoresTrait;
use App\Http\Traits\BeforeSystem\BeforeSystemTrait;

class Classification
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
        $clsf = $paywell_table
                ->where('DNS_PK', $before_brand_id)
                ->where(function ($query) {
                    return $query->where('ITEM_TYPE', 0)
                        ->orWhere('ITEM_TYPE', 3);
                })
                ->orderby('PK', 'DESC')
                ->get();
        foreach($clsf as $cls) {
            $item = [
                'brand_id' => $brand_id,
                'name' => $cls->ITEM_NM,
                'type' => $cls->ITEM_TYPE == 0 ? 0 : 1,
                'is_delete'=> !$cls->IS_USE,                
                'PK' => $cls->PK,
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
        return $res;
    }
}
