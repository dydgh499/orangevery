<?php

namespace App\Http\Controllers\Manager;

use App\Models\Option;
use App\Models\Product;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Requests\Manager\ProductForm;
use App\Http\Requests\Manager\IndexForm;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

/**
 * @group Product API
 *
 * 상품 관리 메뉴에서 사용될 API 입니다. 조회를 제외하고 마스터 이상권한이 요구됩니다.
 */
class ProductController extends Controller
{
    use ManagerTrait, ExtendResponseTrait;
    protected $products;

    public function __construct(Product $products)
    {
        $this->products = $products;
        $this->imgs = [
            'params'    => ['product_img'],
            'folders'   => ['products'],
            'sizes'     => [512],
        ];
    }

    /**
     * 목록출력
     *
     * 가맹점 이상 가능
     *
     * @queryParam search string 검색어(상품 명)
     */
    public function index(IndexForm $request)
    {
        $search = $request->input('search', '');
        $query  = $this->products
            ->join('categorys', 'products.cate_id', '=', 'categorys.id')
            ->where('categorys.brand_id', $request->user()->brand_id)
            ->where('products.name', 'like', "%$search%");
        $data   = $this->getIndexData($request, $query, 'products.id', ['products.*', 'categorys.name as category_name'], 'products.created_at');
        return $this->response(0, $data);
    }

    /**
     * 추가
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(ProductForm $request)
    {
        $data = $request->data();
        $data = $this->saveImages($request, $data, $this->imgs);
        $result = $this->products->create($data);
        return $this->response($result ? 1 : 990);
    }

    /**
     * 단일조회
     *
     * @urlParam id integer required 상품 ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $data = $this->products->where('id', $id)->first();
        return $this->response($data ? 0 : 1000, $data);
    }

    /**
     * 업데이트
     *
     * @urlParam id integer required 상품 ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(ProductForm $request, $id)
    {
        $data = $request->data();
        $data = $this->saveImages($request, $data, $this->imgs);
        $result = $this->products->where('id', $id)->update($data);
        return $this->response($result ? 1 : 990);
    }

    /**
     * 단일삭제
     *
     * @urlParam id integer required 상품 ID
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $result = $this->delete($this->products->where('id', $id), ['products']);
        return $this->response($result);
    }

    /**
     * 상품 하위옵션 조회
     *
     * @urlParam id integer required 상품 ID
     * @return \Illuminate\Http\Response
     */
    public function options($id)
    {
        $options = Option::where('prod_id', $id)->groupsTie();
        return $this->response(0, $options);
    }
}
