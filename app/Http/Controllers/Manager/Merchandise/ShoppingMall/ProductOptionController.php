<?php

namespace App\Http\Controllers\Manager\Merchandise\ShoppingMall;

use App\Models\Merchandise\ShoppingMall\ProductOptionGroup;
use App\Models\Merchandise\ShoppingMall\ProductOption;
use App\Http\Controllers\Ablilty\ShoppingMallWindowInterface;

use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Requests\Manager\Merchandise\ShoppingMall\ProductOptionRequest;
use App\Http\Requests\Manager\IndexRequest;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

/**
 * @group Product Option API
 *
 * 상품 관리 메뉴에서 사용될 옵션 API 입니다.
 */
class ProductOptionController extends Controller
{
    use ManagerTrait, ExtendResponseTrait;
    protected $product_options;

    public function __construct(ProductOption $product_options)
    {
        $this->product_options = $product_options;
        $this->imgs = [
            'params'    => [],
            'cols'      => [],
            'folders'   => [],
            'sizes'     => [],
        ];
    }

    public function initProduct($group_id)
    {
        $group = ProductOptionGroup::where('id', $group_id)->first();
        ShoppingMallWindowInterface::initProductInfo($group->product_id);
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
        $query  = $this->product_options->where('product_options.name', 'like', "%$search%");

        $data = $this->getIndexData($request, $query, 'product_options.id', ['product_options.*', 'products.name as product_name'], 'product_options.created_at');
        return $this->response(0, $data);
    }

    /**
     * 추가
     *
     */
    public function store(ProductOptionRequest $request)
    {
        $data = $request->data();
        $data = $this->saveImages($request, $data, $this->imgs);
        $res = $this->product_options->create($data);
        $this->initProduct($data['group_id']);
        return $this->response($res ? 1 : 990, ['id' => $res->id]);
    }

    /**
     * 단일조회
     *
     * @urlParam id integer required 옵션 ID
     * @return \Illuminate\Http\Response
     */
    public function show(int $id)
    {
        $data = $this->product_options->where('id', $id)->first();
        return $this->response($data ? 0 : 1000, $data);
    }

    /**
     * 업데이트
     *
     * @urlParam id integer옵션 ID
     */
    public function update(ProductOptionRequest $request, int $id)
    {
        $data = $request->data();
        $data = $this->saveImages($request, $data, $this->imgs);
        $res = $this->product_options->where('id', $id)->update($data);
        $this->initProduct($data['group_id']);
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
        $query = $this->product_options->where('id', $id);
        $option = (clone $query)->first();
        $this->initProduct($option->group_id);
        $res = (clone $query)->delete();
        return $this->response($res);
    }
}
