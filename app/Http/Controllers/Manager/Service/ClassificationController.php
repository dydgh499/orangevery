<?php

namespace App\Http\Controllers\Manager\Service;

use App\Http\Controllers\Ablilty\Ablilty;
use App\Http\Controllers\Ablilty\EditAbleWorkTime;
use App\Http\Controllers\Ablilty\ActivityHistoryInterface;

use App\Models\Service\Classification;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Requests\Manager\Service\ClassificationReqeust;
use App\Http\Requests\Manager\IndexRequest;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * @group Classification API
 *
 * 구분 관리 메뉴에서 사용될 API 입니다. 조회를 제외하고 본사 이상권한이 요구됩니다.
 */
class ClassificationController extends Controller
{
    use ManagerTrait, ExtendResponseTrait;
    protected $classifications;
    protected $target;

    public function __construct(Classification $classifications)
    {
        $this->classifications = $classifications;
        $this->target = '구분 정보';
        $this->imgs = [];
    }

    /**
     * 목록출력
     *
     * 가맹점 이상 가능
     */
    public function index(IndexRequest $request)
    {
        $query = $this->classifications
            ->where('is_delete', false)
            ->where('brand_id', $request->user()->brand_id);
        $data = $this->getIndexData($request, $query);
        return $this->response(0, $data);
    }

    /**
     * 추가
     *
     * 대리점 이상 가능
     *
     */
    public function store(ClassificationReqeust $request)
    {
        if(EditAbleWorkTime::validate() === false)
            return $this->extendResponse(1500, '지금은 작업할 수 없습니다.');

        $data = $request->data();
        $res = app(ActivityHistoryInterface::class)->add($this->target, $this->classifications, $data, 'name');
        if($res)
            return $this->response(1, ['id' => $res->id]);    
        else
            return $this->response(990, []);
    }

    /**
     * 단일조회
     *
     * 가맹점 이상 가능
     *
     * @urlParam id integer required 유저 PK
     */
    public function show(Request $request, int $id)
    {
        $data = $this->classifications->where('id', $id)->first();
        return $data ? $this->response(0, $data) : $this->response(1000);
    }

    /**
     * 업데이트
     *
     * 가맹점 이상 가능
     *
     * @urlParam id integer required 유저 PK
     */
    public function update(ClassificationReqeust $request, int $id)
    {
        if(EditAbleWorkTime::validate() === false)
            return $this->extendResponse(1500, '지금은 작업할 수 없습니다.');

        $data = $request->data();
        $query = $this->classifications->where('id', $id);
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
        if(EditAbleWorkTime::validate() === false)
            return $this->extendResponse(1500, '지금은 작업할 수 없습니다.');

        $query  = $this->classifications->where('id', $id);
        $row    = app(ActivityHistoryInterface::class)->destory($this->target, $query, 'name');
        return $this->response(1, ['id' => $id]);   
    }
}
