<?php

namespace App\Http\Controllers\Manager;

use App\Models\Brand;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Requests\Manager\BrandRequest;
use App\Http\Requests\Manager\IndexRequest;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redis;

/**
 * @group Brand API
 *
 * 브랜드 관리 메뉴에서 사용될 API 입니다. 조회를 제외하고 마스터 이상권한이 요구됩니다.
 */
class BrandController extends Controller
{
    use ManagerTrait, ExtendResponseTrait;
    protected $brand;
    protected $imgs;

    public function __construct(Brand $brands)
    {
        $this->brands = $brands;
        $this->imgs = [
            'params'    => [
                'logo_file', 'favicon_file', 'passbook_file',
                'contract_file', 'id_file', 'og_file', 'bsin_lic_file',
            ],
            'cols'  => [
                'logo_img', 'favicon_img', 'passbook_img',
                'contract_img', 'id_img', 'og_img', 'bsin_lic_img',
            ],
            'folders'   => [
                'logos', 'favicons', 'passbooks',
                'contracts', 'ids', 'ogs', 'bsin_lic'
            ],
            'sizes'     => [
                512, 32, 500,
                500, 500, 1200, 500
            ],
        ];
    }

    /**
     * 목록출력
     *
     * 브랜드 이상 가능
     *
     * @queryParam search string 검색어(브랜드 명)
     */
    public function index(IndexRequest $request)
    {
        $search     = $request->input('search', '');
        $brand_id   = $request->user()->brand_id;

        if(isMainBrand($request->user()->brand_id) && $request->user()->tokenCan(50))
            $query = $this->brands;
        else
            $query = $this->brands->where('id', $brand_id);

        $query  = $query
            ->where('is_delete', false)
            ->where('name', 'like', "%$search%");
        $data   = $this->getIndexData($request, $query);
        return $this->response(0, $data);
    }

    /**
     * 추가
     *
     * 개발사 이상 가능
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(BrandRequest $request)
    {
        if($request->user()->tokenCan(50))
        {
            $data = $request->data();
            $data = $this->saveImages($request, $data, $this->imgs);
            $result = $this->brands->create($data);
            return $this->response($result ? 1 : 990);
        }
        else
            return $this->response(951);
    }

    /**
     * 단일조회
     *
     * 브랜드 이상 가능
     *
     * @urlParam id integer required 브랜드 PK
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, $id)
    {
        if($this->authCheck($request->user(), $id, 45))
        {
            $data = $this->brands->where('id', $id)->first();
            return $this->response($data ? 0 : 1000, $data);
        }
        else
            return $this->response(951);
    }

    /**
     * 업데이트
     *
     * 개발사 이상, 또는 로그인한 브랜드 ID와 같은 계정(본인)만 가능
     *
     * @urlParam id integer required 브랜드 PK
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(BrandRequest $request, $id)
    {
        if($this->authCheck($request->user(), $id, 45))
        {
            $data = $request->data();
            $data = $this->saveImages($request, $data, $this->imgs);
            
            $query  = $this->brands->where('id', $id);
            $result = $query->update($data);
            $brand = Redis::set($request->dns, json_encode($query->first()->toArray()));

            return $this->response($result ? 1 : 990);
        }
        else
            return $this->response(951);
    }

    /**
     * 단일삭제
     *
     * 개발사 이상 가능
     *
     * @urlParam id integer required 브랜드 PK
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, $id)
    {
        if($this->authCheck($request->user(), $id, 45))
        {
            $brand = $this->brands->where('id', $id)->first();
            Redis::del($brand->dns);
            $result = $this->delete($this->brands->where('id', $id), ['logo_img', 'favicon_img']);
            return $this->response($result);
        }
        else
            return $this->response(951);
    }
}
