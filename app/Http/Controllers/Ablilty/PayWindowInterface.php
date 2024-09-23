<?php

namespace App\Http\Controllers\Ablilty;

use App\Http\Controllers\Manager\CodeGenerator\GeneratorInterface;
use App\Models\Merchandise\PaymentModule;
use App\Models\Merchandise\PayWindow;
use Illuminate\Support\Facades\Redis;
use Carbon\Carbon;
use Illuminate\Support\Str;

class PayWindowInterface implements GeneratorInterface
{
    static public function publishCode($window_code, $length)
    {
        return $window_code.strtoupper(Str::random($length - strlen($window_code)));
    }

    static public function create($generate_code, $length=8)
    {
        do {
            $window_code = self::publishCode($generate_code, $length);
        }
        while(PayWindow::where('window_code', $window_code)->exists());
        return $window_code;
    }

    static public function bulkCreate($generate_code, $create_count)
    {
        // 사용안함
    }

    static private function getPayWindow($pmod_id)
    {
        $key_name = "pay-window-id:".$pmod_id;
        $pay_window = Redis::get($key_name);
        if($pay_window !== null)
            return json_decode($pay_window, true);
        else
        {
            $pay_window = PayWindow::where('pmod_id', $pmod_id)->first();
            if($pay_window)
            {
                Redis::set($key_name, json_encode($pay_window), 'EX', 600);
                return json_decode(json_encode($pay_window), true);    
            }
            else
                return null;
        }
    }

    static private function getHoldingAbleAt($pay_module)
    {
        if($pay_module->pay_window_extend_hour === 25)
            return Carbon::now()->addYears(10)->format('Y-m-d H:i:s');
        else if($pay_module->module_type === 1)
            return Carbon::now()->addHours($pay_module->pay_window_extend_hour)->format('Y-m-d H:i:s');
        else
            return Carbon::now()->addDays(365)->format('Y-m-d H:i:s');
    }

    static public function renew($pmod_id)
    {
        $data = [
            'pmod_id'   => $pmod_id,
            'pin_code'  => str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT),
            'window_code' => self::create(''), 
        ];
        $pay_window = self::getPayWindow($pmod_id);
        if($pay_window)
        {
            if(Carbon::createFromFormat('Y-m-d H:i:s', $pay_window['holding_able_at']) > Carbon::now())
                return [0, $pay_window];
            else
            {
                $pay_module = PaymentModule::where('id', $pmod_id)->first();
                $data['holding_able_at'] = self::getHoldingAbleAt($pay_module);
                $res = PayWindow::where('pmod_id', $pmod_id)->update($data);
                return [1, $data];    
            }
        }
        else
        {
            $pay_module = PaymentModule::where('id', $pmod_id)->first();
            if($pay_module)
            {   //인증, 간편결제는 만료기간 1년
                $data['holding_able_at'] = self::getHoldingAbleAt($pay_module);
                $res = PayWindow::create($data);
                return [1, $data];    
            }
            else
                return [0, []];
        }
    }

    static public function extend($window_code)
    {
        $holding_able_at = Carbon::now();
        $pay_window = PayWindow::where('window_code', $window_code)->first();
        if($pay_window)
        {
            $pay_module = PaymentModule::where('id', $pay_window->pmod_id)->first();
            $holding_able_at = self::getHoldingAbleAt($pay_module);

            $pay_window->holding_able_at = $holding_able_at;
            $pay_window->save();

            Redis::set("pay-window-id:".$pay_window->id, null, 'EX', 1);
            Redis::set("pay-window-info:".$window_code, null, 'EX', 1);    
        }
        
        return $holding_able_at;
    }

    static public function getPayInfo($window_code)
    {
        $key_name = "pay-window-info:".$window_code;
        $data = Redis::get($key_name);
        if($data !== null)
            return json_decode($data, true);
        else
        {
            $pay_module = PayWindow::join('payment_modules', 'payment_windows.pmod_id', '=', 'payment_modules.id')
                ->join('merchandises', 'payment_modules.mcht_id', '=', 'merchandises.id')
                ->join('payment_gateways', 'payment_modules.pg_id', '=', 'payment_gateways.id')
                ->where('payment_windows.window_code', $window_code)
                ->first([
                    'payment_modules.id',
                    'payment_modules.mcht_id',
                    'payment_modules.is_old_auth',
                    'payment_modules.installment',
                    'payment_modules.module_type',
                    'payment_modules.pay_window_secure_level',

                    'merchandises.use_pay_verification_mobile',
                    'merchandises.use_saleslip_prov',
                    'merchandises.use_saleslip_sell',
                    'merchandises.tax_category_type',
                    'merchandises.mcht_name',
                    'merchandises.contact_num',
                    'merchandises.business_num as mcht_business_num',
                    'merchandises.nick_name',
                    'merchandises.addr as mcht_addr',

                    'payment_gateways.id as pg_id',
                    'payment_gateways.pg_type',
                    'payment_gateways.phone_num',
                    'payment_gateways.company_name',
                    'payment_gateways.business_num',
                    'payment_gateways.rep_name',
                    'payment_gateways.addr',

                    'payment_windows.holding_able_at',
                    'payment_windows.window_code',
                ]);
            if($pay_module)
            {
                $data = [
                    'payment_gateway' => [
                        'id'            => $pay_module->pg_id,
                        'pg_type'       => $pay_module->pg_type,
                        'company_name'  => $pay_module->company_name,
                        'business_num'  => $pay_module->business_num,
                        'phone_num'     => $pay_module->phone_num,
                        'rep_name'      => $pay_module->rep_name,
                        'addr'          => $pay_module->addr,
                    ],
                    'merchandise' => [
                        'id' => $pay_module->mcht_id,
                        'addr' => $pay_module->mcht_addr,
                        'mcht_name' => $pay_module->mcht_name,
                        'nick_name' => $pay_module->nick_name,
                        'contact_num' => $pay_module->contact_num,
                        'business_num' => $pay_module->mcht_business_num,
                        'use_pay_verification_mobile' => $pay_module->use_pay_verification_mobile,
                        'use_saleslip_prov' => $pay_module->use_saleslip_prov,
                        'use_saleslip_sell' => $pay_module->use_saleslip_sell,
                        'tax_category_type' => $pay_module->tax_category_type,
                    ],
                    'payment_module' => [
                        'id'            => $pay_module->id,
                        'mcht_id'       => $pay_module->mcht_id,
                        'is_old_auth'   => $pay_module->is_old_auth,
                        'installment'   => $pay_module->installment,
                        'module_type'   => $pay_module->module_type,
                        'pay_window_secure_level' => $pay_module->pay_window_secure_level
                    ],
                    'pay_window' => [
                        'window_code' => $pay_module->window_code,
                        'holding_able_at' => $pay_module->holding_able_at,
                    ]
                ];
                Redis::set($key_name, json_encode($data), 'EX', 300);
                return $data;
            }
            else
                return null;
        }
    }
    
    static public function getPayParamsCode($param_code)
    {
        $key_name = 'pay-window-params:';
        $params = Redis::get($key_name.$param_code);
        if($params !== null)
            return json_decode($params);
        else
            return null;
    }

    static public function setPayParamsCode($holding_able_at, $request)
    {
        $key_name = 'pay-window-params:';
        $param_code = '';
        do {
            $param_code = self::publishCode('', 5);
        }
        while(Redis::get($key_name.$param_code) !== null);

        $params = [
            'amount'        => $request->amount,
            'item_name'     => $request->item_name,
            'buyer_name'    => $request->buyer_name,
            'buyer_phone'   => $request->buyer_phone,
        ];

        Redis::set($key_name.$param_code, json_encode($params), 'EX', Carbon::now()->diffInSeconds(Carbon::createFromFormat('Y-m-d H:i:s', $holding_able_at)));
        return $param_code;
    }

    static public function auth($window_code, $pin_code)
    {
        return PayWindow::where('window_code', $window_code)->where('pin_code', $pin_code)->exists();
    }
}
