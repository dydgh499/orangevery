<?php

namespace App\Http\Controllers\BeforeSystem;

use App\Http\Traits\StoresTrait;
use App\Http\Traits\BeforeSystem\BeforeSystemTrait;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class PaymentModule
{
    use StoresTrait, BeforeSystemTrait;

    public $paywell, $payvery, $paywell_to_payvery, $current_time;
    public function __construct($pg_types)
    {
        $this->pg_types = $pg_types;
        $this->paywell = [];
        $this->payvery = [];
        $this->paywell_to_payvery = [];
        $this->current_time = date('Y-m-d H:i:s');
    }

    public function connectPGInfo($payvery_pgs, $paywell_to_payvery_pgs, $payvery_pss, $paywell_to_payvery_pss)
    {
        $this->payvery_pgs = $payvery_pgs;
        $this->payvery_pss = $payvery_pss;
        $this->paywell_to_payvery_pgs = $paywell_to_payvery_pgs;
        $this->paywell_to_payvery_pss = $paywell_to_payvery_pss;
    }

    public function connectClsInfo($payvery_cls, $paywell_to_payvery_cls)
    {
        $this->payvery_cls = $payvery_cls;
        $this->paywell_to_payvery_cls = $paywell_to_payvery_cls;
    }

    public function connectMchtInfo($payvery_mchts, $paywell_to_payvery_mchts)
    {
        $this->payvery_mchts = $payvery_mchts;
        $this->paywell_to_payvery_mchts = $paywell_to_payvery_mchts;
    }

    public function getTerminalModule($paywell, $brand_id, $before_brand_id)
    {
        $items = [];
        $mchts = $paywell->table('user')
                ->join('merchandise', 'user.PK', '=', 'merchandise.USER_PK')
                ->join('terminal', 'user.PK', '=', 'terminal.USER_PK')
                ->where('user.DNS_PK', $before_brand_id)
                ->orderby('user.PK', 'DESC')
                ->get();

        foreach($mchts as $mcht) {
            if(isset($this->paywell_to_payvery_mchts[$mcht->USER_PK]) == false)
                continue;
            $item = [
                'brand_id'  => $brand_id,
                'mcht_id' => $this->paywell_to_payvery_mchts[$mcht->USER_PK],
                'pg_id' => $mcht->PGID_PK ? $this->paywell_to_payvery_pgs[$mcht->PGID_PK] : null,
                'ps_id' => $mcht->PG_SEC_PK ? $this->paywell_to_payvery_pss[$mcht->PG_SEC_PK] : null,
                'module_type' => 0,
                'mid' => $mcht->MID,
                'tid' => $mcht->TID,
                'serial_num' => $mcht->SERIAL_NUM,
                'terminal_id' => $mcht->T_TYPE_PK ? $this->paywell_to_payvery_cls[$mcht->T_TYPE_PK] : 0,
                'comm_settle_fee' => $mcht->COMM_PR,
                'comm_settle_type' => $mcht->COMM_PR_CALC_LEVEL,
                'comm_calc_level' => $mcht->COMM_PR_CALC_DAY,
                'under_sales_amt' => $mcht->COMM_SALES_CON,
                'begin_dt' => $mcht->COMM_OPEN_DT,
                'ship_out_dt' => $mcht->RELEASE_DT,
                'ship_out_stat' => $mcht->STATUS,
                'note' => '단말기',
                'USER_PK' => $mcht->USER_PK,
                'created_at' => $this->current_time,
                'updated_at' => $this->current_time,
            ];
            array_push($items, $item);
        }
        return $items;
    }

    public function getHandModule($paywell, $brand_id, $before_brand_id)
    {
        $items = [];
        $mchts = $paywell->table('user')
                ->join('merchandise', 'user.PK', '=', 'merchandise.USER_PK')
                ->where('user.DNS_PK', $before_brand_id)
                ->where('merchandise.M_PG', '!=', 0)
                ->where('merchandise.M_KEY', '!=', '')
                ->orderby('user.PK', 'DESC')
                ->get();

        foreach($mchts as $mcht) {
            $pg = collect($this->payvery_pgs)->first(function($item) use($mcht) { return $item['pg_type'] == $mcht->M_PG; });
            $item = [
                'brand_id'  => $brand_id,
                'mcht_id' => $this->paywell_to_payvery_mchts[$mcht->USER_PK],
                'pg_id' => $pg->id,
                'ps_id' => $mcht->PG_SEC_PK ? $this->paywell_to_payvery_pss[$mcht->PG_SEC_PK] : null,
                'module_type' => 1,
                'api_key' => $mcht->M_KEY,
                'sub_key' => $mcht->M_SUB_KEY,
                'mid' => $mcht->M_MID,
                'tid' => $mcht->M_TID,
                'is_old_auth' => $mcht->USE_HAND_PAY_DTAIL,
                'show_pay_view' => $mcht->USE_HAND_PAY,
                'installment' => $mcht->INSTALLMENT_PERD,
                'pay_dupe_limit' => $mcht->USE_DUPE_TRX,
                'abnormal_trans_limit' => $mcht->M_PAY_Y_LIMIT/10000,
                'pay_month_limit' => $mcht->M_PAY_M_LIMIT/10000,
                'pay_day_limit' => $mcht->M_PAY_D_LIMIT/10000,
                'pay_disable_s_tm' => $mcht->PAY_DISABLE_S_TM,
                'pay_disable_e_tm' => $mcht->PAY_DISABLE_E_TM,
                'note' => '수기',
                'USER_PK' => $mcht->USER_PK,
                'created_at' => $this->current_time,
                'updated_at' => $this->current_time,
            ];
            array_push($items, $item);
        }
        return $items;
    }

    public function getAuthModule($paywell, $brand_id, $before_brand_id)
    {
        $items = [];
        $mchts = $paywell->table('user')
                ->join('merchandise', 'user.PK', '=', 'merchandise.USER_PK')
                ->where('user.DNS_PK', $before_brand_id)
                ->where('merchandise.A_PG', '!=', 0)
                ->where('merchandise.A_KEY', '!=', '')
                ->orderby('user.PK', 'DESC')
                ->get();
        foreach($mchts as $mcht) {
            if(isset($this->paywell_to_payvery_mchts[$mcht->USER_PK]) == false)
                continue;
            $pg = collect($this->payvery_pgs)->first(function($item) use($mcht) { return $item['pg_type'] == $mcht->A_PG; });
            $item = [
                'brand_id'  => $brand_id,
                'mcht_id' => $this->paywell_to_payvery_mchts[$mcht->USER_PK],
                'pg_id' => $pg->id,
                'ps_id' => $mcht->PG_SEC_PK ? $this->paywell_to_payvery_pss[$mcht->PG_SEC_PK] : null,
                'module_type' => 2,
                'api_key' => $mcht->A_KEY,
                'sub_key' => $mcht->A_SUB_KEY,
                'mid' => $mcht->A_MID,
                'tid' => $mcht->A_UID,
                'pay_dupe_limit' => $mcht->USE_DUPE_TRX,
                'abnormal_trans_limit' => $mcht->M_PAY_Y_LIMIT/10000,
                'pay_month_limit' => $mcht->M_PAY_M_LIMIT/10000,
                'pay_day_limit' => $mcht->M_PAY_D_LIMIT/10000,
                'pay_disable_s_tm' => $mcht->PAY_DISABLE_S_TM,
                'pay_disable_e_tm' => $mcht->PAY_DISABLE_E_TM,
                'note' => '인증',
                'USER_PK' => $mcht->USER_PK,
                'created_at' => $this->current_time,
                'updated_at' => $this->current_time,
            ];
            array_push($items, $item);
        }
        return $items;
    }

    public function getEasyModule($paywell, $brand_id, $before_brand_id)
    {
        $items = [];
        $mchts = $paywell->table('user')
                ->join('merchandise', 'user.PK', '=', 'merchandise.USER_PK')
                ->where('user.DNS_PK', $before_brand_id)
                ->where('merchandise.S_PG', '!=', 0)
                ->where('merchandise.S_KEY', '!=', '')
                ->orderby('user.PK', 'DESC')
                ->get();
        foreach($mchts as $mcht) {
            if(isset($this->paywell_to_payvery_mchts[$mcht->USER_PK]) == false)
                continue;
            $pg = collect($this->payvery_pgs)->first(function($item) use($mcht) { return $item['pg_type'] == $mcht->A_PG; });
            $item = [
                'brand_id'  => $brand_id,
                'mcht_id' => $this->paywell_to_payvery_mchts[$mcht->USER_PK],
                'pg_id' => $pg->id,
                'ps_id' => $mcht->PG_SEC_PK ? $this->paywell_to_payvery_pss[$mcht->PG_SEC_PK] : null,
                'module_type' => 3,
                'api_key' => $mcht->S_KEY,
                'sub_key' => $mcht->S_SUB_KEY,
                'mid' => $mcht->S_MID,
                'tid' => $mcht->S_TID,
                'pay_dupe_limit' => $mcht->USE_DUPE_TRX,
                'abnormal_trans_limit' => $mcht->M_PAY_Y_LIMIT/10000,
                'pay_month_limit' => $mcht->M_PAY_M_LIMIT/10000,
                'pay_day_limit' => $mcht->M_PAY_D_LIMIT/10000,
                'pay_disable_s_tm' => $mcht->PAY_DISABLE_S_TM,
                'pay_disable_e_tm' => $mcht->PAY_DISABLE_E_TM,
                'note' => '간편',
                'USER_PK' => $mcht->USER_PK,
                'created_at' => $this->current_time,
                'updated_at' => $this->current_time,
            ];
            array_push($items, $item);
        }
        return $items;
    }

    public function getPaywell($paywell, $brand_id, $before_brand_id)
    {
        $a = $this->getTerminalModule($paywell, $brand_id, $before_brand_id);
        $b = $this->getHandModule($paywell, $brand_id, $before_brand_id);
        $c = $this->getAuthModule($paywell, $brand_id, $before_brand_id);
        $d = $this->getEasyModule($paywell, $brand_id, $before_brand_id);        
        $this->paywell = array_merge(array_merge($a, $b), array_merge($c, $d));
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
        return $res;
    }
}
