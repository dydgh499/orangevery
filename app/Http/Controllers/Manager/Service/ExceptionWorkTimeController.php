<?php

namespace App\Http\Controllers\Manager\Service;

use App\Models\Service\ExceptionWorkTime;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Controllers\Message\MessageController;
use App\Enums\AuthLoginCode;

use App\Http\Controllers\Ablilty\Ablilty;
use App\Http\Controllers\Ablilty\EditAbleWorkTime;
use App\Http\Requests\Manager\Service\ExceptionWorkTimeRequest;
use App\Http\Requests\Manager\IndexRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * @group exception work time API
 *
 */
class ExceptionWorkTimeController extends Controller
{
    use ManagerTrait, ExtendResponseTrait;
    protected $work_times;

    public function __construct(ExceptionWorkTime $work_times)
    {
        $this->work_times = $work_times;
    }

    public function index(Request $request)
    {
        $query  = $this->work_times
            ->join('operators', 'exception_work_times.oper_id', '=', 'operators.id')
            ->where('exception_work_times.brand_id', $request->user()->brand_id);
        $data = $this->getIndexData($request, $query, 'exception_work_times.id', [
            'exception_work_times.*', 'operators.user_name', 'operators.nick_name'
        ], 'exception_work_times.created_at');
        return $this->response(0, $data);
    }
    /**
     * 추가
     *
     * 본사 이상 가능
     */
    public function store(ExceptionWorkTimeRequest $request)
    {
        if($request->user()->tokenCan(40))
        {
            if(EditAbleWorkTime::validate() === false)
                return $this->extendResponse(1500, '지금은 작업할 수 없습니다.');

            [$result, $msg, $datas] = MessageController::operatorPhoneValidate($request);
            if($result === AuthLoginCode::SUCCESS->value)
            {
                $res = $this->work_times->create($request->data());
                return $this->response($res ? 1 : 990, ['id'=>$res->id]);    
            }
            else
                return $this->extendResponse($result, $msg, $datas);
        }
        else
            return $this->response(951);
    }

    /**
     * 단일조회
     *
     * 본사 이상 가능
     *
     * @urlParam id integer required 브랜드 PK
     */
    public function show(Request $request, int $id)
    {
        $data = $this->work_times->where('id', $id)->first();
        if(Ablilty::isBrandCheck($request, $data->brand_id) === false)
            return $this->response(951);
        else
            return $this->response($data ? 0 : 1000, $data);
    }

    /**
     * 업데이트
     *
     * 본사 이상 가능
     * 
     * @urlParam id integer required 브랜드 PK
     */
    public function update(ExceptionWorkTimeRequest $request, int $id)
    {
        if(EditAbleWorkTime::validate() === false)
            return $this->extendResponse(1500, '지금은 작업할 수 없습니다.');

        $data = $this->work_times->where('id', $id)->first();
        if(Ablilty::isBrandCheck($request, $data->brand_id) === false)
            return $this->response(951);
        else
        {
            [$result, $msg, $datas] = MessageController::operatorPhoneValidate($request);
            if($result === AuthLoginCode::SUCCESS->value)
            {
                $res = $this->work_times->where('id', $id)->update($request->data());
                return $this->response($res ? 1 : 990, ['id'=>$id]);
            }
            else
                return $this->extendResponse($result, $msg, $datas);
        }
    }

    /**
     * 단일삭제
     *
     * 본사 이상 가능
     *
     * @urlParam id integer required 브랜드 PK
     */
    public function destroy(Request $request, int $id)
    {
        if(EditAbleWorkTime::validate() === false)
            return $this->extendResponse(1500, '지금은 작업할 수 없습니다.');
        
        $res = $this->work_times->where('id', $id)->delete();
        return $this->response($res ? 1 : 990, ['id'=>$id]);
    }
}
