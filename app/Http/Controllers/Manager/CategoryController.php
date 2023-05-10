<?php

namespace App\Http\Controllers\Manager;

use App\Models\Category;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Requests\Manager\CategoryForm;
use App\Http\Requests\Manager\IndexForm;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

/**
 * @group Category API
 *
 * 카테고리 관리 메뉴에서 사용될 API 입니다. 조회를 제외하고 마스터 이상권한이 요구됩니다.
 */
class CategoryController extends Controller
{
    use ManagerTrait, ExtendResponseTrait;
    protected $categorys;

    public function __construct(Category $categorys)
    {
        $this->categorys = $categorys;
    }

    /**
     * 목록출력
     *
     * 가맹점 이상 가능
     *
     * @queryParam search string 검색어(카테고리 명)
     */
    public function index(IndexForm $request)
    {
        $search = $request->input('search', '');
        $query  = $this->categorys
            ->where('brand_id', $request->user()->brand_id)
            ->where('name', 'like', "%$search%");
        $data   = $this->getIndexData($request, $query);
        return $this->response(0, $data);
    }

    /**
     * 추가
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(CategoryForm $request)
    {
        $data = $request->data();
        $res  = $this->categorys->create($data);
        return $this->response($result ? 1 : 990);
    }

    /**
     * 단일조회
     *
     * @urlParam id integer required 카테고리 ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $data = $this->categorys->where('id', $id)->first();
        return $this->response($data ? 0 : 1000, $data);
    }

    /**
     * 업데이트
     *
     * @urlParam id integer required 카테고리 ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(CategoryForm $request, $id)
    {
        $data = $request->data();
        $res  = $this->categorys->where('id', $id)->update($data);
        return $this->response($res ? 1 : 990);
    }

    /**
     * 단일삭제
     *
     * @urlParam id integer required 카테고리 PK
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $result = $this->delete($this->categorys->where('id', $id));
        return $this->response($result);
    }
}
