<?php

namespace App\Http\Controllers\Manager\Service;

use App\Models\Service\Classification;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Requests\Manager\Service\ClassificationReqeust;
use App\Http\Requests\Manager\IndexRequest;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Enums\HistoryType;

/**
 * @group Classification API
 *
 * 구분 관리 메뉴에서 사용될 API 입니다. 조회를 제외하고 마스터 이상권한이 요구됩니다.
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
        $data = $request->data();
        $res = $this->classifications->create($data);

        operLogging(HistoryType::CREATE, $this->target, [], $data, $data['name']);
        return $this->response($res ? 1 : 990, ['id'=>$res->id]);
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
        $data = $request->data();
        $before = $this->classifications->where('id', $id)->first()->toArray();
        $res = $this->classifications->where('id', $id)->update($data);

        operLogging(HistoryType::UPDATE, $this->target, $before, $data, $data['name']);
        return $this->response($res ? 1 : 990, ['id'=>$id]);
    }

    /**
     * 단일삭제
     *
     * @urlParam id integer required 유저 PK
     */
    public function destroy(Request $request, int $id)
    {
        $res = $this->classifications->where('id', $id)->update(['is_delete'=>true]);
        $data = $this->classifications->where('id', $id)->first();
        if($data)
            operLogging(HistoryType::DELETE, $this->target, $data, ['id' => $id], $data->name);
        return $this->response($res ? 1 : 990, ['id'=>$id]);    
    }
}
