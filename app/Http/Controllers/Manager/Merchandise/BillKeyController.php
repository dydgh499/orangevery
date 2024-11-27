<?php

namespace App\Http\Controllers\Manager\Merchandise;

use App\Models\Merchandise\BillKey;
use App\Models\Merchandise\PaymentModule;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Traits\StoresTrait;

use App\Http\Requests\Manager\IndexRequest;
use App\Http\Requests\Manager\Merchandise\BillKeyCreateRequest;
use App\Http\Controllers\Utils\Comm;

use App\Http\Controllers\Ablilty\Ablilty;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * @group Bill Key API
 *
 * 빌키 API입니다.
 */
class BillKeyController extends Controller
{
    use ManagerTrait, ExtendResponseTrait, StoresTrait;
    protected $bill_keys, $merchandises;

    public function __construct(BillKey $bill_keys)
    {
        $this->bill_keys = $bill_keys;
        $this->target = '빌키';
        $this->imgs = [];
    }
    
    /**
     * 목록출력
     *
     * 운영자 이상 가능
     */
    public function index(IndexRequest $request)
    {
        if(Ablilty::isOperator($request))
        {
            $cols = ['bill_keys.*', 'merchandises.mcht_name', 'payment_modules.note'];
            $search = $request->input('search', '');
            $query = $this->bill_keys
                ->join('payment_modules', 'bill_keys.pmod_id', '=', 'payment_modules.id')
                ->join('merchandises', 'payment_modules.mcht_id', '=', 'merchandises.id')
                ->where('merchandises.brand_id', $request->user()->brand_id)
                ->where('merchandises.is_delete', false)
                ->where(function ($query) use ($search) {
                    return $query->where('merchandises.mcht_name', 'like', "%$search%");
                });            
            $query = globalSalesFilter($query, $request, 'merchandises');
            $query = globalAuthFilter($query, $request, 'merchandises');        
            $query = globalPGFilter($query, $request, 'payment_modules');
                
            $data = $this->getIndexData($request, $query, 'bill_keys.id', $cols, 'bill_keys.created_at');
            return $this->response(0, $data);
        }
        else
            return $this->response(951);
    }

    /**
     * 빌키생성
     *
     * 운영자 이상 가능
     *
     */
    public function store(BillKeyCreateRequest $request)
    {
        $data = $request->data();
        $pay_module = PaymentModule::where('id', $data['pmod_id'])->first();
        if($pay_module)
        {
            if($pay_module->pay_key)
            {
                $data['mid'] = $pay_module->mid;
                $res = Comm::post(env('NOTI_URL', 'http://localhost:81').'/api/v2/pay/bill-key', $data, [
                    'Authorization' => $pay_module->pay_key
                ]);
                if($res['body']['result_cd'] === '0000')
                    return $this->response(1, $res['body']);
                else
                    return $this->apiResponse($res['body']['result_cd'], $res['body']['result_msg'], $res['body']);    
            }
            else
                return $this->apiResponse('PV9999', '결제모듈의 pay key가 존재하지 않습니다.<br>pay key를 생성한 후 빌키를 생성해주세요.');
        }
        else
            return $this->extendResponse('1999', '결제모듈이 존재하지 않습니다.');
    }

    /**
     * 단일조회
     *
     * 운영자 이상 가능
     *
     * @urlParam id integer required 빌키 PK
     */
    public function show(Request $request, int $id)
    {

    }

    /**
     * 업데이트
     *
     * 운영자 이상 가능
     *
     * @urlParam id integer required 빌키 PK
     */
    public function update(Request $request, int $id)
    {

    }

    /**
     * 단일삭제
     *
     * 운영자
     * 
     * @urlParam id integer required 빌키 PK
     */
    public function destroy(Request $request, int $id)
    {
        if(Ablilty::isOperator($request))
        {
            $bill_key = $this->bill_keys->where('id', $id)->first();
            if($bill_key)
            {
                $pay_module = PaymentModule::where('id', $bill_key->pmod_id)->first();
                if($pay_module)
                {
                    if($pay_module->pay_key)
                    {
                        $data = [
                            'mid' => $pay_module->mid,
                            'ord_num' => $request->ord_num,
                            'bill_key' => $bill_key->bill_key,
                        ];                    
                        $res = Comm::destroy(env('NOTI_URL', 'http://localhost:81').'/api/v2/pay/bill-key', $data, [
                            'Authorization' => $pay_module->pay_key
                        ]);
                        if($res['body']['result_cd'] === '0000')
                            return $this->response(1, $res['body']);
                        else
                            return $this->apiResponse($res['body']['result_cd'], $res['body']['result_msg'], $res['body']);  
                    }
                    else
                        return $this->extendResponse('1999', '결제모듈의 pay key가 존재하지 않습니다.<br>pay key를 생성한 후 빌키를 생성해주세요.');          
                }
                else
                    return $this->extendResponse('1999', '결제모듈이 존재하지 않습니다.');
            }
            else
                return $this->extendResponse('1999', '빌키가 존재하지 않습니다.');
        }
        else
            return $this->response(951);
    }

    /**
     * 결제하기
     *
     * 운영자 이상 가능
     *
     * @urlParam id integer required 빌키 PK
     */
    public function hand(Request $request, int $id)
    {   // 테스트를 위한 엔드포인트 입니다.
        if(Ablilty::isOperator($request))
        {
            $bill_key = $this->bill_keys->where('id', $id)->first();
            if($bill_key)
            {
                $pay_module = PaymentModule::where('id', $bill_key->pmod_id)->first();
                if($pay_module)
                {
                    if($pay_module->pay_key)
                    {
                        $data = [
                            'mid'       => $pay_module->mid,
                            'pmod_id'   => $bill_key->pmod_id,
                            'bill_key'  => $bill_key->bill_key,
                            'ord_num'   => $request->ord_num,
                            'item_name' => $request->item_name,
                            'buyer_name' => $request->buyer_name,
                            'buyer_phone'=> $request->buyer_phone,
                            'amount'    => $request->amount,
                        ];
                        $res = Comm::post(env('NOTI_URL', 'http://localhost:81').'/api/v2/pay/bill-key/hand', $data, [
                            'Authorization' => $pay_module->pay_key
                        ]);
                        if($res['body']['result_cd'] === '0000')
                            return $this->response(1, $res['body']);
                        else
                            return $this->apiResponse($res['body']['result_cd'], $res['body']['result_msg'], $res['body']);  
                    }
                    else
                        return $this->extendResponse('1999', '결제모듈의 pay key가 존재하지 않습니다.<br>pay key를 생성한 후 빌키를 생성해주세요.');          
                }  
                else
                    return $this->extendResponse('1999', '결제모듈이 존재하지 않습니다.');
            }
            else
                return $this->extendResponse('1999', '빌키가 존재하지 않습니다.');
        }
        else
            return $this->response(951);
    }
}
