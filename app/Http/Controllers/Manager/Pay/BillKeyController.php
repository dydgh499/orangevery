<?php

namespace App\Http\Controllers\Manager\Pay;


use App\Models\Pay\BillKey;
use App\Models\Pay\PaymentModule;

use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Traits\Models\EncryptDataTrait;

use App\Http\Requests\Manager\IndexRequest;
use App\Http\Requests\Manager\Pay\BillKeyCreateRequest;
use App\Http\Requests\Manager\Pay\BillKeyUpdateRequest;
use App\Http\Requests\Manager\Pay\BillKeyPayRequest;

use App\Http\Controllers\Ablilty\Ablilty;
use App\Http\Controllers\Ablilty\EditAbleWorkTime;
use App\Http\Controllers\Ablilty\ActivityHistoryInterface;

use App\Http\Controllers\Utils\Comm;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

/**
 * @group Bill Key API
 *
 * 빌키 API입니다.
 */
class BillKeyController extends Controller
{
    use ManagerTrait, ExtendResponseTrait, EncryptDataTrait;
    protected $bill_keys, $target;

    public function __construct(BillKey $bill_keys)
    {
        $this->bill_keys = $bill_keys;
        $this->target = '빌키';
    }

    public function getPayModule($bill_id)
    {
        $pay_module = null;
        $bill_key = $this->bill_keys->where('id', $bill_id)->first();
        if($bill_key)
            $pay_module = PaymentModule::where('id', $bill_key->pmod_id)->first();
        return [$bill_key, $pay_module];
    }

    /** 
     * 목록출력
     * 
     * 운영자 이상 가능
    */
    public function index(IndexRequest $request)
    {
        $data = $this->bill_keys
            ->join('payment_modules', 'bill_keys.pmod_id', '=', 'payment_modules.id')
            ->where('payment_modules.brand_id', $request->user()->brand_id)
            ->get(['bill_keys.*']);
        return $this->response(0, $data);
    }

    /**
     * 빌키생성
     *
     * 특정 휴대폰번호로 매핑된 빌키정보들을 조회합니다.<br><b>본인인증 작업이 전처리로 요구됩니다.</b>
     */
    public function store(BillKeyCreateRequest $request)
    {
        if(EditAbleWorkTime::validate() === false)
            return $this->extendResponse(1500, '지금은 작업할 수 없습니다.');
        if(Ablilty::isBrandCheck($request, $request->user()->brand_id) === false)
            return $this->response(951);
        else
        {
            $data = $request->data();
            $pay_module = PaymentModule::where('id', $data['pmod_id'])->first();
            if($pay_module)
            {
                $data['mid'] = $pay_module->mid;
                $res = Comm::post(env('NOTI_URL', 'http://localhost:81').'/api/v2/pay/bill-key', $data, [
                    'Authorization' => $pay_module->api_key
                ]);
                if($res['body']['result_cd'] === '0000')
                    return $this->response(1, $res['body']);
                else
                    return $this->apiResponse($res['body']['result_cd'], $res['body']['result_msg'], $res['body']);    
            }
            else
                return $this->apiResponse('1999', '결제모듈이 존재하지 않습니다,');  
        }
    }

    /**
     * 단일조회 (사용안함)
     *
     * @urlParam id integer required 빌키 PK
     */
    public function show(Request $request, int $id)
    {

    }

    /**
     * 업데이트
     *
     * 특정 휴대폰번호로 매핑된 빌키기본정보를 업데이트 합니다.<br><b>본인인증 작업이 전처리로 요구됩니다.</b>
     *
     * @urlParam id integer required 빌키 PK
     */
    public function update(BillKeyUpdateRequest $request, int $id)
    {
        if(EditAbleWorkTime::validate() === false)
            return $this->extendResponse(1500, '지금은 작업할 수 없습니다.');
        if(Ablilty::isBrandCheck($request, $request->user()->brand_id) === false)
            return $this->response(951);
        else
        {
            $data = $request->data();
            $data['buyer_name'] = $this->aes256_encode($data['buyer_name']);
            $data['buyer_phone'] = $this->aes256_encode($data['buyer_phone']);
            $this->bill_keys->where('id', $id)->update($data);
            return $this->response(0);
        }
    }

    /**
     * 단일삭제
     *
     * 특정 휴대폰번호로 매핑된 빌키정보를 삭제합니다.<br><b>본인인증 작업이 전처리로 요구됩니다.</b>
     * 
     * @urlParam id integer required 빌키 PK
     */
    public function destroy(Request $request, int $id)
    {
        [$bill_key, $pay_module] = $this->getPayModule($id);
        if($bill_key && $pay_module)
        {
            if(EditAbleWorkTime::validate() === false)
                return $this->extendResponse(1500, '지금은 작업할 수 없습니다.');
            if(Ablilty::isBrandCheck($request, $request->user()->brand_id) === false)
                return $this->response(951);
            else
            {
                $data = [
                    'mid' => $pay_module->mid,
                    'ord_num' => $request->ord_num,
                    'bill_key' => $bill_key->bill_key,
                ];
                $res = Comm::destroy(env('NOTI_URL', 'http://localhost:81').'/api/v2/pay/bill-key', $data, [
                    'Authorization' => $pay_module->api_key
                ]);
                if($res['body']['result_cd'] === '0000')
                    return $this->response(1, $res['body']);
                else
                    return $this->apiResponse($res['body']['result_cd'], $res['body']['result_msg'], $res['body']);  
            }
        }
        else
            return $this->apiResponse('1999', '빌키 또는 결제모듈이 존재하지 않습니다,');  
    }

    /**
     * 결제하기
     *
     * 빌키결제
     *
     * @urlParam id integer required 빌키 PK
     */
    public function pay(BillKeyPayRequest $request, int $id)
    {
        [$bill_key, $pay_module] = $this->getPayModule($id);
        if($bill_key && $pay_module)
        {
            $data = array_merge($request->data(), [
                'mid'       => $pay_module->mid,
                'pmod_id'   => $bill_key->pmod_id,
                'bill_key'  => $bill_key->bill_key,
            ]);
            $res = Comm::post(env('NOTI_URL', 'http://localhost:81').'/api/v2/pay/bill-key/hand', $data, [
                'Authorization' => $pay_module->api_key
            ]);
            if($res['body']['result_cd'] === '0000')
                return $this->response(1, $res['body']);
            else
                return $this->apiResponse($res['body']['result_cd'], $res['body']['result_msg'], $res['body']);  
        }
        else
            return $this->apiResponse('1999', '빌키 또는 결제모듈이 존재하지 않습니다,');  
    }
}
