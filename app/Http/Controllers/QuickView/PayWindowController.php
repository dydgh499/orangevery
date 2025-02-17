<?php
namespace App\Http\Controllers\QuickView;

use App\Models\Transaction;

use App\Http\Traits\ExtendResponseTrait;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Ablilty\PayWindowInterface;
use App\Http\Controllers\Ablilty\ShoppingMallWindowInterface;

use Illuminate\Http\Request;
use Carbon\Carbon;

class PayWindowController extends Controller
{
    use ExtendResponseTrait;
    
    public function __construct()
    {

    }

    /**
     * 결제창 정보 
     */
    public function testWindow(Request $request)
    {
        [$code, $data] = PayWindowInterface::renew($request->pmod_id);
        if($data)
        {
            $pay_module = PayWindowInterface::getPayInfo($data['window_code']);
            if($pay_module)
            {
                if(Carbon::createFromFormat('Y-m-d H:i:s', $pay_module['pay_window']['holding_able_at']) > Carbon::now())
                    return $this->response(0, $pay_module);
                else
                    return $this->extendResponse(1999, '만료된 결제창 입니다.');
            }
            else
                return $this->extendResponse(1999, '존재하지 않은 결제창 입니다.');    
        }
        else
            return $this->response(951);
    }
    
    /** 
     * 결제창 갱신
    */
    public function renew(Request $request, int $pmod_id)
    {
        [$code, $data] = PayWindowInterface::renew($pmod_id);
        if($request->item_name !== null)
            $data['param_code'] = PayWindowInterface::setPayParamsCode($data['holding_able_at'], $request);

        return $this->response($code, $data);
    }

    /** 
     * 결제창 연장
    */
    public function extend(Request $request, string $window_code)
    {
        $pay_module = PayWindowInterface::getPayInfo($window_code);
        if($pay_module)
        {
            if(Carbon::createFromFormat('Y-m-d H:i:s', $pay_module['pay_window']['holding_able_at']) > Carbon::now())
            {
                $holding_able_at = PayWindowInterface::extend($window_code);
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


    /** 
     * 결제창 정보
    */
    public function window(Request $request, string $window_code)
    {
        $pay_module = PayWindowInterface::getPayInfo($window_code);
        if($pay_module)
        {
            if(Carbon::createFromFormat('Y-m-d H:i:s', $pay_module['pay_window']['holding_able_at']) > Carbon::now())
            {
                if($request->pc != null)
                    $pay_module['params'] = PayWindowInterface::getPayParamsCode($request->pc);
                if($request->sw != null && $request->pi != null)
                    $pay_module['params'] = ShoppingMallWindowInterface::getPayWindowProductInfo($request->sw, $request->pi);
                return $this->response(0, $pay_module);
            }
            else
                return $this->extendResponse(1999, '만료된 결제창 입니다.');
        }
        else
            return $this->extendResponse(1999, '존재하지 않은 결제창 입니다.');        
    }

    /** 
     * 결제창 인증
    */
    public function auth(Request $request, string $window_code)
    {
        $pay_module = PayWindowInterface::getPayInfo($window_code);
        if($pay_module)
        {
            if(Carbon::createFromFormat('Y-m-d H:i:s', $pay_module['pay_window']['holding_able_at']) > Carbon::now())
            {
                if(PayWindowInterface::auth($window_code, $request->pin_code))
                    return $this->response(0);
                else
                    return $this->extendResponse(1999, 'PIN 번호가 잘못되었습니다.');
            }
            else
                return $this->extendResponse(1999, '만료된 결제창 입니다.');
        }
        else
            return $this->extendResponse(1999, '존재하지 않은 결제창 입니다.');        
    }

    /** 
     * 매출전표
    */
    public function salesSlip(Request $request, string $ord_num)
    {
        $data = Transaction::join('merchandises', 'transactions.mcht_id', '=', 'merchandises.id')
            ->where('transactions.brand_id', $request->brand_id)
            ->where('transactions.ord_num', $ord_num)
            ->where('transactions.is_cancel', $request->is_cancel)
            ->first([
                'merchandises.use_saleslip_prov',
                'merchandises.tax_category_type',
                'merchandises.contact_num',
                'merchandises.mcht_name',
                'merchandises.business_num',
                'merchandises.nick_name',
                'merchandises.addr',
                'transactions.*',
            ]);
        if($data)
        {
            $pg = PayWindowInterface::getPaymentGateway($data->pg_id);
            if(count($pg))
            {
                $result = [
                    'merchandise' => [
                        'id' => $data->mcht_id,
                        'addr' => $data->addr,
                        'mcht_name' => $data->mcht_name,
                        'nick_name' => $data->nick_name,
                        'contact_num' => $data->contact_num,
                        'business_num' => $data->business_num,
                        'use_saleslip_prov' => $data->use_saleslip_prov,
                        'tax_category_type' => $data->tax_category_type,
                    ],
                    'transactions' => [
                        'id'            => $data->id,
                        'pg_id'         => $data->pg_id,
                        'pmod_id'       => $data->pmod_id,
                        'is_cancel'     => $data->is_cancel,
                        'trx_dttm'      => $data->trx_dt." ".$data->trx_tm,
                        'module_type'   => $data->module_type,
                        'amount'        => $data->amount,
                        'item_name'     => $data->item_name,
                        'buyer_name'    => $data->buyer_name,
                        'card_num'      => $data->card_num,
                        'appr_num'      => $data->appr_num,
                        'acquirer'      => $data->acquirer,
                        'issuer'        => $data->issuer,
                        'installment'   => $data->installment,
                        'ord_num'       => $data->ord_num,
                    ]
                ];
                $result['payment_gateway'] = $pg;
                if($result['transactions']['is_cancel'])
                    $result['transactions']['cxl_dttm'] = $data->cxl_dt." ".$data->cxl_tm;
                return $this->response(0, $result);
            }
            else
                return $this->extendResponse(1999, '존재하지 않는 원천사 입니다.');
        }
        else
            return $this->extendResponse(1999, '존재하지 거래건 입니다.');
    }
}
