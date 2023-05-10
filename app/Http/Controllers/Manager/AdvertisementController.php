<?php

namespace App\Http\Controllers\Manager;

use App\Models\Advertisement;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Requests\Manager\AdvertisementForm;
use App\Http\Requests\Manager\IndexForm;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * @group Advertisement API
 *
 * 광고 관리 메뉴에서 사용될 API 입니다. 조회를 제외하고 마스터 이상권한이 요구됩니다.
 */
class AdvertisementController extends Controller
{
    use ManagerTrait, ExtendResponseTrait;
    protected $advertisements;

    public function __construct(Advertisement $advertisements)
    {
        $this->advertisements = $advertisements;
        $this->imgs = [
            'params'    => ['ad_img'],
            'folders'   => ['advertisements'],
            'sizes'     => [1024],
        ];
    }

    /**
     * 목록출력
     *
     * 가맹점 이상 가능
     *
     * @queryParam search string 검색어(광고명)
     */
    public function index(IndexForm $request)
    {
        $search = $request->input('search', '');
        $query  = $this->advertisements
            ->where('brand_id', $request->user()->brand_id)
            ->where('ad_name', 'like', "%$search%");
        $data   = $this->getIndexData($request, $query);
        return $this->response(0, $data);
    }

    /**
     * 추가
     *
     * 마스터 이상 가능
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(AdvertisementForm $request)
    {
        $data = $request->data();
        $data = $this->saveImages($request, $data, $this->imgs);
        $result = $this->advertisements->create($data);
        return $this->response($result ? 1 : 990);
    }

    /**
     * 단일조회
     *
     * 마스터 이상 가능
     *
     * @urlParam id integer required 광고 PK
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $data = $this->advertisements->where('id', $id)->first();
        return $this->response($data ? 0 : 1000, $data);
    }

    /**
     * 업데이트
     *
     * 마스터 이상 가능
     *
     * @urlParam id integer required 광고 PK
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(AdvertisementForm $request, $id)
    {
        $data = $request->data();
        $data = $this->saveImages($request, $data, $this->imgs);
        $result = $this->advertisements->where('id', $id)->update($data);
        return $this->response($result ? 1 : 990);
    }

    /**
     * 단일삭제
     *
     * 마스터 이상 가능
     *
     * @urlParam id integer required 광고 PK
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $result = $this->delete($this->advertisements->where('id', $id), ['ad_img']);
        return $this->response($result);
    }
}
