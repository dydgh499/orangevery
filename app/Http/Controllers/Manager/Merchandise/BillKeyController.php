<?php

namespace App\Http\Controllers\Manager\Merchandise;

use App\Enums\AuthLoginCode;

use App\Models\Merchandise\BillKey;
use App\Models\Merchandise\PaymentModule;

use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Traits\Models\EncryptDataTrait;

use App\Http\Requests\Manager\IndexRequest;
use App\Http\Requests\Manager\Merchandise\BillKeyIndexRequest;
use App\Http\Requests\Manager\Merchandise\BillKeyCreateRequest;
use App\Http\Requests\Manager\Merchandise\BillKeyUpdateRequest;
use App\Http\Requests\Manager\Merchandise\BillKeyPayRequest;

use App\Http\Controllers\Utils\Comm;
use App\Http\Controllers\Ablilty\Ablilty;
use App\Http\Controllers\Ablilty\PayWindowInterface;
use App\Http\Controllers\Message\MessageController;
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
    
    public function defaultValidate($request, string $window_code)
    {
        // TODO: token 인증 변경 필요
        $result = 0;
        // [$result, $msg, $datas] = MessageController::operatorPhoneValidate($request);
        if($result === 0)
        {
            $pay_window = PayWindowInterface::getPayInfo($window_code);
            if($pay_window)
            {
                $pay_module = PaymentModule::where('id', $pay_window['payment_module']['id'])->first();
                if($pay_module)
                    return [0, '', $pay_window, $pay_module];
                else
                    return [1999, '결제모듈이 존재하지 않습니다.', null, null];
            }
            else
                return [1999, '존재하지 않은 결제창 입니다.', null, null];
        }
        else
            return [$result, '', null, null];
    }

    public function billKeyValidate($request, string $window_code, int $id)
    {
        [$result, $msg, $pay_window, $pay_module] = $this->defaultValidate($request, $window_code);
        if($result === 0)
        {
            $bill_key = $this->bill_keys->where('id', $id)->first();
            if($bill_key)
            {
                if($pay_window['payment_module']['id'] === $bill_key->pmod_id)
                    return [0, '', $bill_key, $pay_module];
                else
                    return [951, '', $bill_key, null];
            }
            else
                return [1999, '존재하지 빌키 입니다.', null, null];
        }
        else
            return [$result, $msg, null, null];
    }

    /**
     * 목록출력
     *
     * 운영자 이상 가능
     */
    public function managerIndex(IndexRequest $request)
    {
        $cols = ['bill_keys.*', 'merchandises.mcht_name'];
        $search = $request->input('search', '');
        $query = $this->bill_keys
            ->join('payment_modules', 'bill_keys.pmod_id', '=', 'payment_modules.id')
            ->join('merchandises', 'payment_modules.mcht_id', '=', 'merchandises.id')
            ->where('payment_modules.brand_id', $request->user()->brand_id);

        if($search)
        {
            $query = $query->where(function ($query) use ($search) {
                return $query->where('bill_keys.card_num', 'like', "%".$search."%");
            });
        }
        if(Ablilty::isMerchandise($request))
            $query = $query->where('merchandises.id', $request->user()->id);
        else if(Ablilty::isSalesforce($request))
            return $this->response(951);
        else
            $cols[] = 'payment_modules.pg_id';

        $data = $this->getIndexData($request, $query, 'bill_keys.id', $cols, 'bill_keys.created_at');
        return $this->response(0, $data);
    }
    /** 
     * 빌키정보 조회
     * 
     * 특정 휴대폰번호로 매핑된 빌키정보들을 조회합니다.<br><b>본인인증 작업이 전처리로 요구됩니다.</b>
    */
    public function index(BillKeyIndexRequest $request, string $window_code)
    {
        $request->validate(['token' => 'required', 'buyer_phone'=>'required']);
        [$result, $msg, $pay_window, $pay_module] = $this->defaultValidate($request, $window_code);
        if($result === 0)
        {
            $bill_keys = $this->bill_keys
                ->where('pmod_id', $pay_window['payment_module']['id'])
                ->where('buyer_phone', $this->aes256_encode($request->buyer_phone))
                ->get();
            return $this->response(0, $bill_keys);
        }
        else if($result === 951)
            return $this->response(951);
        else
            return $this->extendResponse(1999, $msg);
    }

    /**
     * 빌키생성
     *
     * 특정 휴대폰번호로 매핑된 빌키정보들을 조회합니다.<br><b>본인인증 작업이 전처리로 요구됩니다.</b>
     */
    public function store(BillKeyCreateRequest $request, string $window_code)
    {
        [$result, $msg, $pay_window, $pay_module] = $this->defaultValidate($request, $window_code);
        if($result === 0)
        {
            $data = $request->data();
            $data['mid'] = $pay_module->mid;
            $res = Comm::post(env('NOTI_URL', 'http://localhost:81').'/api/v2/pay/bill-key', $data, [
                'Authorization' => $pay_module->pay_key
            ]);
            if($res['body']['result_cd'] === '0000')
                return $this->response(1, $res['body']);
            else
                return $this->apiResponse($res['body']['result_cd'], $res['body']['result_msg'], $res['body']);    
        }
        else if($result === 951)
            return $this->response(951);
        else
            return $this->extendResponse(1999, $msg);
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
    public function update(BillKeyUpdateRequest $request, string $window_code, int $id)
    {
        [$result, $msg, $bill_key, $pay_module] = $this->billKeyValidate($request, $window_code, $id);
        if($result === 0)
        {
            $data = $request->data();
            $data['buyer_name'] = $this->aes256_encode($data['buyer_name']);
            $data['buyer_phone'] = $this->aes256_encode($data['buyer_phone']);
            $this->bill_keys->where('id', $id)->update($data);
            return $this->response(0);
        }
        else if($result === 951)
            return $this->response(951);
        else
            return $this->extendResponse(1999, $msg);
    }

    /**
     * 단일삭제
     *
     * 특정 휴대폰번호로 매핑된 빌키정보를 삭제합니다.<br><b>본인인증 작업이 전처리로 요구됩니다.</b>
     * 
     * @urlParam id integer required 빌키 PK
     */
    public function destroy(Request $request, string $window_code, int $id)
    {
        [$result, $msg, $bill_key, $pay_module] = $this->billKeyValidate($request, $window_code, $id);
        if($result === 0)
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
        else if($result === 951)
            return $this->response(951);
        else
            return $this->extendResponse(1999, $msg);
    }

    /**
     * 결제하기
     *
     * 빌키결제
     *
     * @urlParam id integer required 빌키 PK
     */
    public function pay(BillKeyPayRequest $request, string $window_code, int $id)
    {
        [$result, $msg, $bill_key, $pay_module] = $this->billKeyValidate($request, $window_code, $id);
        if($result === 0)
        {
            $data = array_merge($request->data(), [
                'mid'       => $pay_module->mid,
                'pmod_id'   => $bill_key->pmod_id,
                'bill_key'  => $bill_key->bill_key,
            ]);
            $res = Comm::post(env('NOTI_URL', 'http://localhost:81').'/api/v2/pay/bill-key/hand', $data, [
                'Authorization' => $pay_module->pay_key
            ]);
            if($res['body']['result_cd'] === '0000')
                return $this->response(1, $res['body']);
            else
                return $this->apiResponse($res['body']['result_cd'], $res['body']['result_msg'], $res['body']);  
        }
        else if($result === 951)
            return $this->response(951);
        else
            return $this->extendResponse(1999, $msg);
    }
}
