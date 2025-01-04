<?php

namespace App\Http\Controllers\Manager\Merchandise\ShoppingMall;

use App\Models\Merchandise\ShoppingMall\ProductOptionGroup;
use App\Models\Merchandise\ShoppingMall\ProductOption;
use App\Http\Controllers\Ablilty\ShoppingMallWindowInterface;

use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Requests\Manager\Merchandise\ShoppingMall\ProductOptionGroupRequest;
use App\Http\Requests\Manager\IndexRequest;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

/**
 * @group Product Option Group API
 *
 * 상품 관리 메뉴에서 사용될 그룹 API 입니다.
 */
class ProductOptionGroupController extends Controller
{
    use ManagerTrait, ExtendResponseTrait;
    protected $product_option_groups;

    public function __construct(ProductOptionGroup $product_option_groups)
    {
        $this->product_option_groups = $product_option_groups;
        $this->imgs = [
            'params'    => [],
            'cols'      => [],
            'folders'   => [],
            'sizes'     => [],
        ];
    }

    /**
     * 목록출력
     *
     * 가맹점 이상 가능
     *
     * @queryParam search string 검색어(옵션명)
     */
    public function index(IndexRequest $request)
    {
        $search = $request->input('search', '');
        $query  = $this->product_option_groups
            ->where('product_option_groups.name', 'like', "%$search%");

        $data = $this->getIndexData($request, $query, 'product_option_groups.id', ['product_option_groups.*', 'products.name as product_name'], 'product_option_groups.created_at');
        return $this->response(0, $data);
    }

    /**
     * 추가
     *
     */
    public function store(ProductOptionGroupRequest $request)
    {
        $data = $request->data();
        $data = $this->saveImages($request, $data, $this->imgs);
        $res  = $this->product_option_groups->create($data);
        ShoppingMallWindowInterface::initProductInfo($data['product_id']);
        return $this->response($res ? 1 : 990, ['id'=>$res->id]);
    }

    /**
     * 단일조회
     *
     * @urlParam id integer required 옵션 ID
     * @return \Illuminate\Http\Response
     */
    public function show(int $id)
    {
        $data = $this->product_option_groups->where('id', $id)->first();
        return $this->response($data ? 0 : 1000, $data);
    }

    /**
     * 업데이트
     *
     * @urlParam id integer옵션 ID
     */
    public function update(ProductOptionGroupRequest $request, int $id)
    {
        $data = $request->data();
        $data = $this->saveImages($request, $data, $this->imgs);
        $res  = $this->product_option_groups->where('id', $id)->update($data);
        ShoppingMallWindowInterface::initProductInfo($data['product_id']);
        return $this->response($res ? 1 : 990, ['id' => $id]);
    }

    /**
     * 단일삭제
     *
     * @urlParam id integer required 옵션 ID
     * @return \Illuminate\Http\Response
     */
    public function destroy(int $id)
    {
        $query = $this->product_option_groups->where('id', $id);
        $group = (clone $query)->first();
        ShoppingMallWindowInterface::initProductInfo($group->product_id);
        $res = (clone $query)->delete();
        return $this->response($res);
    }
}
