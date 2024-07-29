<?php

namespace App\Http\Controllers\Manager;

use App\Models\PaymentSection;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Requests\Manager\PaySectionRequest;
use App\Http\Requests\Manager\IndexRequest;

use App\Http\Controllers\Ablilty\Ablilty;
use App\Http\Controllers\Ablilty\EditAbleWorkTime;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * @group Payment Section API
 *
 * PG사 구간 API입니다.
 */
class PaymentSectionController extends Controller
{
    use ManagerTrait, ExtendResponseTrait;
    protected $pay_sections;

    public function __construct(PaymentSection $pay_sections)
    {
        $this->pay_sections = $pay_sections;
        $this->imgs = [];
    }

    /**
     * 목록출력
     *
     * 운영자 이상 가능
     */
    public function index(IndexRequest $request)
    {
        $query = $this->pay_sections
                ->where('brand_id', $request->user()->brand_id)
                ->where('is_delete', false);
        $data = $this->getIndexData($request, $query);
        return $this->response(0, $data);
    }

    /**
     * 추가
     *
     * 운영자 이상 가능
     *
     */
    public function store(PaySectionRequest $request)
    {
        if(EditAbleWorkTime::validate() === false)
            return $this->extendResponse(1500, '지금은 작업할 수 없습니다.');

        $user = $request->data();
        $res = $this->pay_sections->create($user);
        return $this->response($res ? 1 : 990, ['id'=>$res->id]);
    }

    /**
     * 단일조회
     *
     * 운영자 이상 가능
     *
     * @urlParam id integer required 유저 PK
     */
    public function show(Request $request, int $id)
    {
        $data = $this->pay_sections->where('id', $id)->first();
        return $data ? $this->response(0, $data) : $this->response(1000);
    }

    /**
     * 업데이트
     *
     * 운영자 이상 가능
     *
     * @urlParam id integer required 유저 PK
     */
    public function update(PaySectionRequest $request, int $id)
    {
        if(EditAbleWorkTime::validate() === false)
            return $this->extendResponse(1500, '지금은 작업할 수 없습니다.');

        $data = $request->data();
        $res = $this->pay_sections->where('id', $id)->update($data);
        return $this->response($res ? 1 : 990, ['id'=>$id]);
    }

    /**
     * 단일삭제
     *
     * @urlParam id integer required 유저 PK
     */
    public function destroy(Request $request, int $id)
    {
        if(EditAbleWorkTime::validate() === false)
            return $this->extendResponse(1500, '지금은 작업할 수 없습니다.');

        $res = $this->delete($this->pay_sections->where('id', $id));
        return $this->response($res ? 1 : 990, ['id'=>$id]);
    }
}
