<?php

namespace App\Http\Controllers\BeforeSystem;

use App\Http\Traits\StoresTrait;
use App\Http\Traits\BeforeSystem\BeforeSystemTrait;
use Illuminate\Support\Facades\Hash;

class Merchandise
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

    public function connectSalesInfo($payvery_sales, $paywell_to_payvery_sales)
    {
        $this->payvery_sales = $payvery_sales;
        $this->paywell_to_payvery_sales = $paywell_to_payvery_sales;
    }

    public function connectClsInfo($payvery_cls, $paywell_to_payvery_cls)
    {
        $this->payvery_cls = $payvery_cls;
        $this->paywell_to_payvery_cls = $paywell_to_payvery_cls;
    }

    public function getPaywell($paywell, $brand_id, $before_brand_id)
    {
        $items = [];
        $cols = [
            'merchandise.*', 'user.ID', 'user.PW', 
            'user.REP_NM', 'user.SECTORS', 'user.RESIDENT_NUM', 'user.BUSINESS_NUM',
            'user.PHONE', 'user.ADDR', 'user.NICK_NM', 'user.NOTE',
        ];
        $mchts = $paywell->table('user')
                ->join('merchandise', 'user.PK', '=', 'merchandise.USER_PK')
                ->where('user.DNS_PK', $before_brand_id)
                ->orderby('user.PK', 'DESC')
                ->get($cols);

        $privacys = $this->getPaywellPrivacy($paywell, $mchts, 'USER_PK');
        foreach($mchts as $mcht) {
            $privacy = $privacys->first(function($item) use ($mcht) {return $item->USER_PK == $mcht->USER_PK;});
            
            $agcy_pk    = $mcht->AGCY_PK && isset($this->paywell_to_payvery_sales[$mcht->AGCY_PK]) ? $this->paywell_to_payvery_sales[$mcht->AGCY_PK] : null;
            $dist_pk    = $mcht->DIST_PK && isset($this->paywell_to_payvery_sales[$mcht->DIST_PK]) ? $this->paywell_to_payvery_sales[$mcht->DIST_PK] : null;
            $branch_pk  = $mcht->BRANCH_PK && isset($this->paywell_to_payvery_sales[$mcht->BRANCH_PK]) ? $this->paywell_to_payvery_sales[$mcht->BRANCH_PK] : null;
            $slsfc_pk   = $mcht->SLSFC_PK && isset($this->paywell_to_payvery_sales[$mcht->SLSFC_PK]) ? $this->paywell_to_payvery_sales[$mcht->SLSFC_PK] : null;
            
            $item = [
                'brand_id'  => $brand_id,
                'user_name' => $mcht->ID,
                'user_pw'   => Hash::make($mcht->PW.$this->current_time),
                'mcht_name' => $mcht->NICK_NM,
                'nick_name' => $mcht->REP_NM,
                'sector'    => $mcht->SECTORS,
                'resident_num'=> $mcht->RESIDENT_NUM,
                'business_num'=> $mcht->BUSINESS_NUM,
                'phone_num' => $mcht->PHONE,
                'addr'      => $mcht->ADDR,
                'acct_num'  => $privacy ? $privacy->ACCT_NUM : null,
                'acct_name'  => $privacy ? $privacy->ACCT_NM : null,
                'acct_bank_name'  => $privacy ? $privacy->ACCT_BANK : null,
                'acct_bank_code'  => $privacy ? sprintf("%03d", (int)$privacy->ACCT_BANK_CD) : null,
                'passbook_img'  => null,
                'id_img'  => $privacy ? 'https://paywell.pe.kr'.$privacy->ID_NUM_IMG : null,
                'contract_img'  => $privacy ? 'https://paywell.pe.kr'.$privacy->CTRT_IMG : null,
                'bsin_lic_img'  => $privacy ? 'https://paywell.pe.kr'.$privacy->BUSINESS_IMG : null,
                'sales1_id' => $agcy_pk,
                'sales2_id' => $dist_pk,
                'sales3_id' => $branch_pk,
                'sales4_id' => $slsfc_pk,
                'sales1_fee' => $mcht->AGCY_FEE,
                'sales2_fee' => $mcht->DIST_FEE,
                'sales3_fee' => $mcht->BRANCH_FEE,
                'sales4_fee' => $mcht->SLSFC_FEE,
                'hold_fee' => $mcht->HOLD_AMT_FEE,
                'trx_fee' => $mcht->MD_FEE,
                'custom_id' => $mcht->CST_FL ? $this->paywell_to_payvery_cls[$mcht->CST_FL] : null,
                'use_saleslip_prov' => (boolean)$mcht->USE_SALESLIP_HEAD_INFO,
                'is_show_fee' => (boolean)$mcht->IS_SHOW_FEE,
                'note'      => $mcht->NOTE,
                'USER_PK' => $mcht->USER_PK,
                'created_at' => $this->current_time,
                'updated_at' => $this->current_time,
            ];
            array_push($items, $item);
        }
        $this->paywell = $items;
    }

    public function setPayvery($payvery_table, $brand_id)
    {
        $items = $this->getPayveryFormat($this->paywell, 'USER_PK');
        $res   = $this->manyInsert($payvery_table, $items);
        if($res)
        {
            $this->payvery = $this->getPayvery($payvery_table, $brand_id, $this->current_time);
            $this->paywell_to_payvery = $this->connect($this->payvery, $this->paywell, 'USER_PK');
        }
    }
}
