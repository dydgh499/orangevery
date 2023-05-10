<?php

namespace App\Http\Controllers\Manager;

use App\Models\CouponPublish;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Requests\Manager\CouponPublishForm;
use App\Http\Requests\Manager\IndexForm;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * @group Coupon publish API
 *
 * 쿠폰발급 관리 메뉴에서 사용될 API 입니다. 조회를 제외하고 마스터 이상권한이 요구됩니다.
 */
class CouponPublishController extends Controller
{
    use ManagerTrait, ExtendResponseTrait;
    protected $couponPublishs;

    public function __construct(CouponPublish $couponPublishs)
    {
        $this->couponPublishs = $couponPublishs;
    }

    /**
     * 목록출력
     *
     * 가맹점 이상 가능
     *
     * @queryParam search string 검색어(바코드명)
     */
    public function index(IndexForm $request)
    {
        $search = $request->input('search', '');
        $query  = $this->couponPublishs
            ->join('coupon_models', 'coupon_publishs.coupon_id', '=', 'coupon_models.id')
            ->where('coupon_models.brand_id', $request->user()->brand_id)
            ->where('coupon_publishs.barcode_num', 'like', "%$search%");
        $data   = $this->getIndexData($request, $query, 'coupon_publishs.id', ['coupon_publishs.*', 'coupon_models.name', 'coupon_models.coupon_img', 'coupon_models.sale_amt']);
        return $this->response(0, $data);
    }

    /**
     * 추가
     *
     * @return \Illuminate\Http\Response
     */
    public function store(CouponPublishForm $request)
    {
        $data = $request->data();
        $data['barcode_num'] = '';
        $result = $this->couponPublishs->create($data);
        return $this->response($result ? 1 : 990);
    }

    /**
     * 단일조회
     *
     * @urlParam id integer required 발행쿠폰 PK
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $data = $this->couponPublishs->where('id', $id)->first();
        return $this->response($data ? 0 : 1000, $data);
    }

    /**
     * 업데이트
     *
     * @urlParam id integer required 발행쿠폰 PK
     * @return \Illuminate\Http\Response
     */
    public function update(CouponPublishForm $request, $id)
    {
        $data = $request->data();
        $result = $this->couponPublishs->where('id', $id)->update($data);
        return $this->response($result ? 1 : 990);
    }

    /**
     * 단일삭제
     *
     * @urlParam id integer 발행쿠폰 PK
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $result = $this->delete($this->couponPublishs->where('id', $id), []);
        return $this->response($result);
    }
}
