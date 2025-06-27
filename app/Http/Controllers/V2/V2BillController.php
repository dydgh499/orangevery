<?php
namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\DB;
use App\Http\Traits\Util\HttpTrait;
use App\Http\Traits\Util\APITrait;

use App\Http\Requests\V2\BillCreateRequest;
use App\Http\Requests\V2\BillDeleteRequest;
use App\Http\Requests\V2\BillPayRequest;

use App\Http\Controllers\Option\BillPayValidate;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class V2BillController extends Controller
{
    use HttpTrait, APITrait;
    public $ver, $db;

    public function __construct()
    {
        $this->ver = 2;
        $this->db = DB::connection('onequeue');
    }

    public function getBillInfo(array $request)
    {
        
        $mid     = Arr::get($request, 'mid', '');
        $tid     = Arr::get($request, 'tid', '');
        $pmod_id  = Arr::get($request, 'pmod_id', '');
        $bill_key = Arr::get($request, 'bill_key', '');
        /*
        $mid = $request->input('mid', '');
        $tid = $request->input('tid', '');
        $pmod_id = $request->input('pmod_id', '');
        $bill_key = $request->input('bill_key', '');
        Log::info($mid);
        */
        $bill_key = BillPayValidate::getBillKey($mid, $tid, $pmod_id, $bill_key);
        $pmod = BillPayValidate::getPayModule($this->db, $mid, $tid, $pmod_id);
        return [$bill_key, $pmod];
    }

    public function handleBillKeyCreate(array $request)
    {
        // Log::info('billkey before', ['payload' => $request->all()]);
        //[$bill_key, $pmod] = $this->getBillInfo($request);
        
        [$billKey, $pmod] = $this->getBillInfo($request);
        
            if ($pmod->module_type === 4) {
                $noti = BillPayValidate::getBillCreateFormat($pmod, $request);
                [$code, $message, $pg_name] = BillPayValidate::createValidate($this->db, $pmod, $noti);
                if ($code !== '0000') {
                    return ['success' => false, 'code' => $code, 'msg' => $message];
                }
                $pg = BillPayValidate::getPG($noti, $pmod, 1, $this->ver, $pg_name);
                if ($pg) {
                    return ['success' => true, 'result' => $pg->billKeyCreate()];
                } else {
                    return ['success' => false, 'code' => 'PV405', 'msg' => '지원하지 않는 PG사입니다.'];
                }
            } else {
                return ['success' => false, 'code' => 'PV452', 'msg' => '빌키사용이 불가한 결제모듈입니다.'];
            }
        /*
        if ($pmod) {
        }
        return ['success' => false, 'code' => 'PV406', 'msg' => '가맹점을 찾을 수 없습니다.'];
        */
    }

/*
    public function delete(BillDeleteRequest $request)
    {
        [$bill_key, $pmod] = $this->getBillInfo($request);
        if($bill_key)
        {
            if($pmod)
            {
                $noti = BillPayValidate::getBillDeleteFormat($pmod, $request);
                [$code, $message, $pg_name] = BillPayValidate::deleteValidate($this->db, $pmod, $noti);
                if($code !== '0000')
                    return $this->apiErrorResponse($code, $message);
                else
                {
                    $pg = BillPayValidate::getPG($noti, $pmod, 1, $this->ver, $pg_name);
                    if($pg)
                        return $pg->billkeyRemove($bill_key->ori_bill_key);
                    else
                        return $this->apiErrorResponse('PV405', '지원하지 않는 PG사입니다.');
                }
            }
            else
                return $this->apiErrorResponse('PV406', '가맹점을 찾을 수 없습니다.');
        }
        else
            return $this->apiErrorResponse('PV470', '빌키가 존재하지 않습니다.');
    }

    public function pay(BillPayRequest $request)
    {
        [$bill_key, $pmod] = $this->getBillInfo($request);
        if($bill_key)
        {
            if($pmod)
            {
                if($pmod->module_type === 4)
                {
                    $noti = BillPayValidate::getBillPayFormat($pmod, $request, $bill_key);
                    [$code, $message, $pg_name] = BillPayValidate::payValidate($this->db, $pmod, $noti, $bill_key);
                    if($code !== '0000')
                        return $this->apiErrorResponse($code, $message);
                    else
                    {
                        $pg = BillPayValidate::getPG($noti, $pmod, 1, $this->ver, $pg_name);
                        if($pg)
                            return $pg->billkeyPay($bill_key->ori_bill_key);
                        else
                            return $this->apiErrorResponse('PV405', '지원하지 않는 PG사입니다.');
                    }
                }
                else
                    return $this->apiErrorResponse('PV452', '빌키사용이 불가한 결제모듈입니다.');
            }
            else
                return $this->apiErrorResponse('PV406', '가맹점을 찾을 수 없습니다.');
        }
        else
            return $this->apiErrorResponse('PV470', '빌키가 존재하지 않습니다.');
    }
*/
}