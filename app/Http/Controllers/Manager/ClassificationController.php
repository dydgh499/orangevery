<?php

namespace App\Http\Controllers\Manager;

use App\Models\Classification;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Requests\Manager\ClassificationReqeust;
use App\Http\Requests\Manager\IndexRequest;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ClassificationController extends Controller
{
    use ManagerTrait, ExtendResponseTrait;
    protected $classifications;

    public function __construct(Classification $classifications)
    {
        $this->classifications = $classifications;
        $this->imgs = [];
    }

    /**
     * 목록출력
     *
     * 가맹점 이상 가능
     */
    public function index(IndexRequest $request)
    {
        $query = $this->classifications->where('brand_id', $request->user()->brand_id);
        $data = $this->getIndexData($request, $query);
        return $this->response(0, $data);
    }

    /**
     * 추가
     *
     * 대리점 이상 가능
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(ClassificationReqeust $request)
    {
        $user = $request->data();
        $res = $this->classifications->create($user);
        return $this->response($res ? 1 : 990);
    }

    /**
     * 단일조회
     *
     * 가맹점 이상 가능
     *
     * @urlParam id integer required 유저 PK
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, $id)
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(ClassificationReqeust $request, $id)
    {
        $data = $request->data();
        $res = $this->classifications->where('id', $id)->update($data);
        return $this->response($res ? 1 : 990);
    }

    /**
     * 단일삭제
     *
     * @urlParam id integer required 유저 PK
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, $id)
    {
        $res = $this->classifications->where('id', $id)->update(['is_delete'=>true]);
        return $this->response($res ? 1 : 990);
    }
}
