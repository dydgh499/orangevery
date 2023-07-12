<?php

namespace App\Http\Controllers\BeforeSystem;

use App\Models\Options\PvOptions;
use App\Models\Options\ThemeCSS;
use App\Http\Traits\StoresTrait;
use App\Http\Traits\BeforeSystem\BeforeSystemTrait;

class Brand
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
        $brand = $paywell_table
                ->where('PK', $before_brand_id)
                ->first();

            $theme_css = new ThemeCSS('[]');
            $pv_options = new PvOptions('[]');

            $theme_css->main_color = $brand->TM_CLR;
            $pv_options->free->use_hand_pay = (boolean)$brand->USE_HAND_PAY;
            $pv_options->free->use_auth_pay = (boolean)$brand->USE_AUTH_PAY;
            $pv_options->free->use_simple_pay = (boolean)$brand->USE_SIMPLE_PAY;
            $pv_options->free->sales_slip['merchandise']['comepany_name'] = $brand->SEL_NICK_NM;
            $pv_options->free->sales_slip['merchandise']['rep_name'] = $brand->SEL_REP_NM;
            $pv_options->free->sales_slip['merchandise']['phone_num'] = $brand->SEL_PHONE;
            $pv_options->free->sales_slip['merchandise']['business_num'] = $brand->SEL_BUSINESS_NUM;
            $pv_options->free->sales_slip['merchandise']['addr'] = $brand->SEL_ADDR;

            $pv_options->paid->use_dup_pay_validation = (boolean)$brand->USE_DUPE_TRX;
            $pv_options->paid->subsidiary_use_control = (boolean)$brand->USE_MD_ENABLED;
            $pv_options->paid->use_acct_verification = (boolean)$brand->USE_ACCT_VALID;
            $pv_options->paid->use_realtime_deposit = false;
            $pv_options->paid->use_hand_pay_drct = (boolean)$brand->USE_HAND_PAY_DRCT;
            $pv_options->paid->use_issuer_filter = (boolean)$brand->USE_MD_CRD_FL;
            $pv_options->paid->use_forb_pay_time = (boolean)$brand->USE_PAY_DISABLE_TM;
            $pv_options->paid->use_hand_pay_sms = (boolean)$brand->USE_HAND_PAY_SMS;
            $pv_options->paid->use_pay_limit = (boolean)$brand->USE_PAY_LIMIT;
            
            $pv_options->auth->levels['dev_use'] = (boolean)$brand->USE_DEV;
            $pv_options->auth->levels['sales5_use'] = false;
            $pv_options->auth->levels['sales4_use'] = (boolean)$brand->USE_SF;
            $pv_options->auth->levels['sales3_use'] = true;
            $pv_options->auth->levels['sales2_use'] = true;
            $pv_options->auth->levels['sales1_use'] = true;
            $pv_options->auth->levels['sales0_use'] = false;

            $pv_options->auth->levels['dev_name'] = '개발사';
            $pv_options->auth->levels['sales5_name'] = '상위 영업점';
            $pv_options->auth->levels['sales4_name'] = '영업점';
            $pv_options->auth->levels['sales3_name'] = '지사';
            $pv_options->auth->levels['sales2_name'] = '총판';
            $pv_options->auth->levels['sales1_name'] = '대리점';
            $pv_options->auth->levels['sales0_name'] = '하위 대리점';

            $item = [
                'name' => $brand->SVC_NM,
                'theme_css' => json_encode($theme_css),
                'logo_img' => 'https://paywell.pe.kr'.$brand->LOGO,
                'favicon_img' => 'https://paywell.pe.kr'.$brand->FVC,
                'passbook_img' => 'https://paywell.pe.kr'.$brand->ACCT_IMG,
                'id_img' => null,
                'contract_img' => 'https://paywell.pe.kr'.$brand->CTRT_IMG,
                'og_img' => 'https://paywell.pe.kr'.$brand->LOGO,
                'bsin_lic_img' => 'https://paywell.pe.kr'.$brand->BSIN_IMG,
                'og_description' => '환영합니다! 🎉 '.$brand->SVC_NM.'입니다.',
                'note' => $brand->MEMO,
                
                'company_name' => $brand->COMPANY_NM,
                'pvcy_rep_name' => $brand->REP_NM,
                'ceo_name' => $brand->REP_NM,
                'addr' => $brand->SEL_ADDR,
                'phone_num' => $brand->PHONE,
                'fax_num' => $brand->PHONE,
                'pv_options' => json_encode($pv_options),
                
                'deposit_day' => $brand->DEPOSIT_DAY,
                'deposit_amount' => $brand->DEPOSIT_AMOUNT,
                'last_dpst_at' => $brand->LAST_DPST_DTTM,
                'updated_at' => $this->current_time,
            ];    
        $this->paywell = $item;
    }

    public function setPayvery($payvery_table, $brand_id)
    {
        $res = $payvery_table->where('id', $brand_id)->update($this->paywell);
        if($res)
        {
            $this->payvery = json_decode(
                    json_encode($payvery_table
                        ->where('id', $brand_id)
                        ->where('updated_at', $this->current_time)
                        ->first())
                , true);
        }
    }
}
