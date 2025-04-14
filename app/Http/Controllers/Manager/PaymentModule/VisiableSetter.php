<?php

namespace App\Http\Controllers\Manager\PaymentModule;

use App\Http\Controllers\Ablilty\Ablilty;

class VisiableSetter
{
    static public function topPGCols()
    {
        return [
            'p_mid', 'api_key', 'sub_key',
            'pg_id', 'ps_id',
        ];

    }

    static public function financeVanCols()
    {
        return [
            'use_realtime_deposit',
            // 'fin_id', 'fin_trx_delay', 
        ];
    }

    static public function paymentCols()
    {
        return [
            'cxl_type', 'filter_issuers', 
        ];
    }

    static function hiddenSensitiveInfo()
    {
        return array_merge(self::topPGCols(), self::financeVanCols(), self::paymentCols());
            /*
            'abnormal_trans_limit', 
            'pay_dupe_limit',

            'pay_disable_e_tm',
            'pay_disable_s_tm',
            'pay_dupe_least',

            'pay_day_limit',
            'pay_month_limit',
            'pay_single_limit',
            'pay_year_limit',
            'payment_term_min',
            */
    }

    static function salesforceHidden()
    {

    }

    static function set($payment_module, $request)
    {
        if(Ablilty::isMerchandise($request))
            $payment_module->makeHidden(self::hiddenSensitiveInfo());
        else if(Ablilty::isSalesforce($request) && $request->user()->auth_level === 0)
            $payment_module->makeHidden(self::hiddenSensitiveInfo());
        return $payment_module;
    }
}
