<?php

namespace App\Http\Controllers\Manager\CodeGenerator;

use App\Http\Controllers\Manager\CodeGenerator\GeneratorInterface;
use App\Models\Merchandise\PaymentModule;
use App\Models\Merchandise\PayWindow;
use Illuminate\Support\Facades\Redis;
use Carbon\Carbon;
use Illuminate\Support\Str;

class PayWindowGenerator implements GeneratorInterface
{
    static public function getNewPayWindowCode($window_code)
    {
        return $window_code.strtoupper(Str::random(10 - strlen($window_code)));
    }

    static public function create($generate_code)
    {
        do {
            $window_code = self::getNewPayWindowCode($generate_code);
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

    static public function renew($pmod_id)
    {
        $data = [
            'pmod_id'   => $pmod_id,
            'pin_code'  => str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT),
            'window_code' => self::create(''), 
            'holding_able_at' => Carbon::now()->addHours(1)->format('Y-m-d H:i:s'),
        ];
        $pay_window = self::getPayWindow($pmod_id);
        if($pay_window)
        {
            if(Carbon::createFromFormat('Y-m-d H:i:s', $pay_window['holding_able_at']) > Carbon::now())
                return [0, $pay_window];
            else
            {
                $res = PayWindow::where('pmod_id', $pmod_id)->update($data);
                return [1, $data];    
            }
        }
        else
        {
            $pay_module = PaymentModule::where('id', $pmod_id)->first();
            if($pay_module)
            {   //인증, 간편결제는 만료기간 1년
                if($pay_module->module_type > 1)
                    $data['holding_able_at'] = Carbon::now()->addDays(365)->format('Y-m-d H:i:s');

                $res = PayWindow::create($data);
                return [1, $data];    
            }
            else
                return [0, []];
        }
    }

    static public function getPayInfo($window_code)
    {
        $key_name = "pay-window-info:".$window_code;
        $data = Redis::get($key_name);
        if($data !== null)
            return json_decode($data, true);
        else
        {
            $pay_module = PayWindow::join('payment_modules', 'pay_windows.pmod_id', '=', 'payment_modules.id')
                ->join('merchandises', 'payment_modules.mcht_id', '=', 'merchandises.id')
                ->join('payment_gateways', 'payment_modules.pg_id', '=', 'payment_gateways.id')
                ->where('pay_windows.window_code', $window_code)
                ->first([
                    'payment_modules.id',
                    'payment_modules.mcht_id',
                    'payment_modules.is_old_auth',
                    'payment_modules.installment',
                    'payment_modules.module_type',
                    'merchandises.use_pay_verification_mobile',
                    'merchandises.mcht_name',
                    'merchandises.business_num as mcht_business_num',
                    'merchandises.nick_name',
                    'merchandises.addr as mcht_addr',
                    'pay_windows.holding_able_at',
                    'payment_gateways.pg_type',
                    'payment_gateways.company_name',
                    'payment_gateways.business_num',
                    'payment_gateways.rep_name',
                    'payment_gateways.addr',
                ]);
            if($pay_module)
            {
                $data = [
                    'payment_gateway' => [
                        'pg_type'       => $pay_module->pg_type,
                        'company_name'  => $pay_module->company_name,
                        'business_num'  => $pay_module->business_num,
                        'rep_name'      => $pay_module->rep_name,
                    ],
                    'merchandise' => [
                        'id' => $pay_module->mcht_id,
                        'mcht_name' => $pay_module->mcht_name,
                        'business_num' => $pay_module->mcht_business_num,
                        'nick_name' => $pay_module->nick_name,
                        'addr' => $pay_module->mcht_addr,
                        'use_pay_verification_mobile' => $pay_module->use_pay_verification_mobile,
                    ],
                    'payment_module' => [
                        'id'            => $pay_module->id,
                        'mcht_id'       => $pay_module->mcht_id,
                        'is_old_auth'   => $pay_module->is_old_auth,
                        'installment'   => $pay_module->installment,
                        'module_type'   => $pay_module->module_type,
                    ],
                    'pay_window' => [
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
}
