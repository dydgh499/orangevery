<?php

namespace App\Http\Controllers\BeforeSystem;

use App\Http\Traits\StoresTrait;
use App\Http\Traits\BeforeSystem\BeforeSystemTrait;
use Illuminate\Support\Facades\Hash;

class Salesforce
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

    public function getPaywell($paywell, $brand_id, $before_brand_id)
    {
        $items = [];
        $sales = $paywell->table('user')
                ->whereIn('level', [15,20,30,35])
                ->where('DNS_PK', $before_brand_id)
                ->orderby('PK', 'DESC')
                ->get();

        $privacys = $this->getPaywellPrivacy($paywell, $sales);
        foreach($sales as $sale) {
            $privacy = $privacys->first(function($item) use ($sale) {return $item->USER_PK == $sale->PK;});
            if($sale->LEVEL == 35)
                $level = 25;
            else if($sale->LEVEL == 30)
                $level = 20;
            else if($sale->LEVEL == 20)
                $level = 17;
            else if($sale->LEVEL == 15)
                $level = 15;

            $item = [
                'brand_id'  => $brand_id,
                'user_name' => $sale->ID,
                'user_pw'   => Hash::make($sale->PW.$this->current_time),
                'sales_name' => $sale->NICK_NM,
                'nick_name'  => $sale->REP_NM,
                'resident_num'=> $sale->RESIDENT_NUM,
                'business_num'=> $sale->BUSINESS_NUM,
                'phone_num' => $sale->PHONE,
                'addr'      => $sale->ADDR,
                'acct_num'  => $privacy ? $privacy->ACCT_NUM : null,
                'acct_name'  => $privacy ? $privacy->ACCT_NM : null,
                'acct_bank_name'  => $privacy ? $privacy->ACCT_BANK : null,
                'acct_bank_code'  => $privacy ? sprintf("%03d", (int)$privacy->ACCT_BANK_CD) : null,
                'passbook_img'  => null,
                'id_img'  => $privacy ? 'https://paywell.pe.kr'.$privacy->ID_NUM_IMG : null,
                'contract_img'  => $privacy ? 'https://paywell.pe.kr'.$privacy->CTRT_IMG : null,
                'bsin_lic_img'  => $privacy ? 'https://paywell.pe.kr'.$privacy->BUSINESS_IMG : null,
                'settle_tax_type'=> $sale->TAX_TYPE,
                'settle_cycle' => 0,
                'settle_day' => null,
                'view_type' => (boolean)$sale->VIEW_TYPE,
                'note'      => $sale->NOTE,
                'level'     => $level,
                'PK' => $sale->PK,
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
