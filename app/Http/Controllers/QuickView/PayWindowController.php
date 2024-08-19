<?php

namespace App\Http\Controllers\QuickView;

use App\Http\Traits\ExtendResponseTrait;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Manager\CodeGenerator\PayWindowGenerator;

use Illuminate\Http\Request;
use Carbon\Carbon;

class PayWindowController extends Controller
{
    use ExtendResponseTrait;
    
    public function __construct()
    {

    }

    public function renew(Request $request, int $pmod_id)
    {
        [$code, $data] = PayWindowGenerator::renew($pmod_id);
        if($request->item_name !== null)
            $data['param_code'] = PayWindowGenerator::setPayParamsCode($data['window_code'], $request);

        return $this->response($code, $data);
    }

    public function extend(Request $request, string $window_code)
    {
        $pay_module = PayWindowGenerator::getPayInfo($window_code);
        if($pay_module)
        {
            if(Carbon::createFromFormat('Y-m-d H:i:s', $pay_module['pay_window']['holding_able_at']) > Carbon::now())
            {
                $holding_able_at = PayWindowGenerator::extend($window_code);
                return $this->response(0, [
                    'holding_able_at' => $holding_able_at,
                ]);
            }
            else
                return $this->extendResponse(1999, '만료된 결제창 입니다.');

        }
        else
            return $this->extendResponse(1999, '존재하지 않은 결제창 입니다.');        
    }

    public function window(Request $request, string $window_code)
    {
        $pay_module = PayWindowGenerator::getPayInfo($window_code);
        if($pay_module)
        {
            if(Carbon::createFromFormat('Y-m-d H:i:s', $pay_module['pay_window']['holding_able_at']) > Carbon::now())
            {
                if($request->pc != null)
                    $pay_module['params'] = PayWindowGenerator::getPayParamsCode($request->pc);
                return $this->response(0, $pay_module);
            }
            else
                return $this->extendResponse(1999, '만료된 결제창 입니다.');
        }
        else
            return $this->extendResponse(1999, '존재하지 않은 결제창 입니다.');        
    }

    public function signIn(Request $request)
    {

    }
}
