<?php

namespace App\Http\Controllers\Manager;

use App\Models\Merchandise;
use App\Models\PaymentModule;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * @group Salesforce-Batch API
 *
 * 영업자의 일괄적용 group 입니다.
 */
class SalesforceBatchController extends Controller
{
    use ManagerTrait, ExtendResponseTrait;
    protected $merchandises, $payModules;

    public function __construct(Merchandise $merchandises, PaymentModule $payModules)
    {
        $this->merchandises = $merchandises;
        $this->payModules   = $payModules;
    }

    private function getSalesKeys($request)
    {
        $idx  = globalLevelByIndex($request->level);
        $sales_key = [
            'sales_fee' => 'sales'.$idx.'_fee',
            'sales_id'  => 'sales'.$idx.'_id',
        ];
        return $sales_key;
    }

    /**
     * 수수료율 적용 
     */
    public function setFee(Request $request)
    {
        $sales_key = $this->getSalesKeys($request);

        $mchts = $this->merchandises->where('brand_id', $request->user()->id)
                ->where($sales_key['sales_id'], $request->id)
                ->get();



        $this->merchandises->where('brand_id', $request->user()->id)
                ->where($sales_key['sales_id'], $request->id)
                ->update([$sales_key['sales_fee'] => $request->sales_fee/100]);
        /*
        $bf_trx_fee = $mcht[$sales_key['sales_fee']];
        $aft_trx_fee = $request->sales_fee/100;
        $bf_sales_id = $mcht[$sales_key['sales_id']];
        $aft_sales_id = $request->sales_id;

        $data['bf_trx_fee']     = $bf_trx_fee;
        $data['aft_trx_fee']    = $aft_trx_fee;
        $udpt_data[$sales_key['sales_fee']] = $aft_trx_fee;

        $data['bf_sales_id'] = $bf_sales_id;
        $data['aft_sales_id'] = $aft_sales_id;
        $udpt_data[$sales_key['sales_id']] = $aft_sales_id;
        */
        return $this->response(1);
    }

    /**
     * 커스텀 필터 적용 
     */
    public function setCustomFilter(Request $request)
    {
        return $this->response(1);

    }

    /**
     * 이상거래 한도 적용 
     */
    public function setAbnormalTransLimit(Request $request)
    {
        return $this->response(1);

    }

    /**
     * 중복결제 허용회수 적용 
     */
    public function setDupPayValidation(Request $request)
    {
        return $this->response(1);

    }
    
    /**
     * 결제한도 적용 
     */
    public function setPayLimit(Request $request)
    {
        return $this->response(1);

    }

    /**
     * 결제금지시간 적용 
     */
    public function setForbiddenPayTime(Request $request)
    {
        return $this->response(1);

    }

    /**
     * 결제창 노출여부 적용 
     */
    public function setShowPayView(Request $request)
    {
        return $this->response(1);

    }
}
