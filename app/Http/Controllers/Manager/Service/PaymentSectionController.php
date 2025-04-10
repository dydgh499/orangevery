<?php

namespace App\Http\Controllers\Manager\Service;

use App\Models\Service\PaymentSection;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Requests\Manager\Service\PaySectionRequest;
use App\Http\Requests\Manager\IndexRequest;

use App\Http\Controllers\Ablilty\Ablilty;
use App\Http\Controllers\Ablilty\EditAbleWorkTime;
use App\Http\Controllers\Ablilty\ActivityHistoryInterface;

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
    protected $target;

    public function __construct(PaymentSection $pay_sections)
    {
        $this->pay_sections = $pay_sections;
        $this->target       = '구간';
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

        $data = $request->data();
        $res = app(ActivityHistoryInterface::class)->add($this->target, $this->pay_sections, $data, 'name');
        if($res)
            return $this->response(1, ['id'=>$res->id]);
        else
            return $this->response(990);
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
        $query      = $this->pay_sections->where('id', $id);
        $section    = $query->first();
        $data       = $request->data();

        if(EditAbleWorkTime::validate() === false)
            return $this->extendResponse(1500, '지금은 작업할 수 없습니다.');
        if(Ablilty::isBrandCheck($request, $section->brand_id) === false)
            return $this->response(951);

        $row = app(ActivityHistoryInterface::class)->update($this->target, $query, $data, 'name');
        if($row)
            return $this->response(1, ['id' => $id]);
        else
            return $this->response(990);
    }

    /**
     * 단일삭제
     *
     * @urlParam id integer required 유저 PK
     */
    public function destroy(Request $request, int $id)
    {
        $query      = $this->pay_sections->where('id', $id);
        $section    = $query->first();
        
        if(EditAbleWorkTime::validate() === false)
            return $this->extendResponse(1500, '지금은 작업할 수 없습니다.');
        if(Ablilty::isBrandCheck($request, $section->brand_id) === false)
            return $this->response(951);

        $row = app(ActivityHistoryInterface::class)->destory($this->target, $query, 'name');
        return $this->response(1, ['id' => $id]);
    }
}
