<?php

namespace App\Http\Controllers\Manager\Service;

use App\Http\Controllers\Auth\AuthOperatorIP;
use App\Models\Service\OperatorIP;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Traits\Models\EncryptDataTrait;

use App\Enums\AuthLoginCode;
use App\Http\Controllers\Ablilty\Ablilty;
use App\Http\Controllers\Ablilty\EditAbleWorkTime;
use App\Http\Controllers\Message\MessageController;

use App\Http\Requests\Manager\Service\OperatorIPRequest;
use App\Http\Requests\Manager\IndexRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * @group operator IP API
 *
 * 운영자 허용 IP API 입니다.
 */
class OperatorIPController extends Controller
{
    use ManagerTrait, ExtendResponseTrait, EncryptDataTrait;
    protected $operator_ips;

    public function __construct(OperatorIP $operator_ips)
    {
        $this->operator_ips = $operator_ips;
    }

    /**
     * 목록출력
     */
    public function index(IndexRequest $request)
    {
        if($request->user()->level >= 40)
        {
            $query = $this->operator_ips->where('brand_id', $request->user()->brand_id);
            $data  = $this->getIndexData($request, $query);
            return $this->response(0, $data);    
        }
        else
            return $this->response(951);
    }

    /**
     * 추가
     *
     */
    public function store(OperatorIPRequest $request)
    {
        if(EditAbleWorkTime::validate() === false)
            return $this->extendResponse(1500, '지금은 작업할 수 없습니다.');

        [$result, $msg, $datas] = MessageController::operatorPhoneValidate($request);
        if($result === AuthLoginCode::SUCCESS->value)
        {
            $data = $request->data();
            $data['brand_id'] = $request->user()->brand_id;
    
            $res = $this->operator_ips->create($data);
            $ips = $this->operator_ips->where('brand_id', $data['brand_id'])
                ->get()->pluck('enable_ip')->all();
            AuthOperatorIP::set($data['brand_id'], $ips);
    
            return $this->response($res ? 1 : 990, [
                'id' => $res->id, 
            ]);            
        }
        else
            return $this->extendResponse($result, $msg, $datas);
    }

    /**
     * 단일조회
     *
     * @urlParam id integer required PK
     * @return \Illuminate\Http\Response
     */
    public function show(int $id)
    {

    }

    /**
     * 업데이트
     *
     * @urlParam id integer required PK
     * @return \Illuminate\Http\Response
     */
    public function update(OperatorIPRequest $request, int $id)
    {
        $operator_ip = $this->operator_ips->where('id', $id)->first();

        if(EditAbleWorkTime::validate() === false)
            return $this->extendResponse(1500, '지금은 작업할 수 없습니다.');
        if(Ablilty::isBrandCheck($request, $operator_ip->brand_id) === false)
            return $this->response(951);
        else
        {
            [$result, $msg, $datas] = MessageController::operatorPhoneValidate($request);
            if($result === AuthLoginCode::SUCCESS->value)
            {
                $operator_ip->enable_ip = $request->enable_ip;
                $operator_ip->save();
        
                $ips = $this->operator_ips
                    ->where('brand_id', $request->user()->brand_id)
                    ->get()->pluck('enable_ip')->all();
                AuthOperatorIP::set($operator_ip->brand_id, $ips);
    
                return $this->response(1, ['id' => $id]);
            }
            else
                return $this->extendResponse($result, $msg, $datas);
        }
    }

    /**
     * 단일삭제
     *
     * @urlParam id integer required PK
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, int $id)
    {
        if($request->user()->level >= 40)
        {
            $operator_ip = $this->operator_ips->where('id', $id)->first();
            if(EditAbleWorkTime::validate() === false)
                return $this->extendResponse(1500, '지금은 작업할 수 없습니다.');
            if(Ablilty::isBrandCheck($request, $operator_ip->brand_id) === false)
                return $this->response(951);
            else
            {
                [$result, $msg, $datas] = MessageController::operatorPhoneValidate($request);
                if($result === AuthLoginCode::SUCCESS->value)
                {
                    $res = $this->operator_ips->where('id', $id)->delete();
                    return $this->response($res ? 1 : 990, [
                        'id' => $id
                    ]);        
                }
                else
                    return $this->extendResponse($result, $msg, $datas);
            }
        }
        else
            return $this->response(951);
    }

    static public function addIP($brand_id, $enable_ip)
    {
        OperatorIP::create([
            'brand_id' => $brand_id,
            'enable_ip' => $enable_ip,
        ]);
        $ips = OperatorIP::where('brand_id', $brand_id)->get()->pluck('enable_ip')->all();
        AuthOperatorIP::set($brand_id, $ips);
    }
}
