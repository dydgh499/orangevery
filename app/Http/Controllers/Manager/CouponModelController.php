<?php

namespace App\Http\Controllers\Manager;

use App\Models\CouponModel;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Requests\Manager\CouponModelForm;
use App\Http\Requests\Manager\IndexForm;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * @group Coupon model API
 *
 * 쿠폰모델 관리 메뉴에서 사용될 API 입니다. 조회를 제외하고 마스터 이상권한이 요구됩니다.
 */
class CouponModelController extends Controller
{
    use ManagerTrait, ExtendResponseTrait;
    protected $couponModels;

    public function __construct(CouponModel $couponModels)
    {
        $this->couponModels = $couponModels;
        $this->imgs = [
            'params'    => ['coupon_img'],
            'folders'   => ['coupons'],
            'sizes'     => [512],
        ];
    }

    /**
     * 목록출력
     *
     * 마스터 이상 가능
     *
     * @queryParam search string 검색어(쿠폰 모델명)
     */
    public function index(IndexForm $request)
    {
        $search = $request->input('search', '');
        $query  = $this->couponModels
            ->where('brand_id', $request->user()->brand_id)
            ->where('name', 'like', "%$search%");
        return $this->response(0, $this->getIndexData($request, $query));
    }

    /**
     * 추가
     *
     * @return \Illuminate\Http\Response
     */
    public function store(CouponModelForm $request)
    {
        $data = $request->data();
        $data = $this->saveImages($request, $data, $this->imgs);
        $result = $this->couponModels->create($data);
        return $this->response($result ? 1 : 990);
    }

    /**
     * 단일조회
     *
     * @urlParam id integer required 쿠폰 PK
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $data = $this->couponModels->where('id', $id)->first();
        return $this->response($data ? 0 : 1000, $data);
    }

    /**
     * 업데이트
     *
     * @urlParam id integer required 쿠폰 PK
     * @return \Illuminate\Http\Response
     */
    public function update(CouponModelForm $request, $id)
    {
        $data = $request->data();
        $data = $this->saveImages($request, $data, $this->imgs);
        $result = $this->couponModels->where('id', $id)->update($data);
        return $this->response($result ? 1 : 990);
    }

    /**
     * 단일삭제
     *
     * @urlParam id integer required 쿠폰 PK
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $result = $this->delete($this->couponModels->where('id', $id), ['coupon_img']);
        return $this->response($result);
    }
}
