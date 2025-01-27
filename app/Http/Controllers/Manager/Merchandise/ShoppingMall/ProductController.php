<?php

namespace App\Http\Controllers\Manager\Merchandise\ShoppingMall;

use App\Models\Merchandise\ShoppingMall\Product;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;

use App\Http\Requests\Manager\IndexRequest;
use App\Http\Requests\Manager\Merchandise\ShoppingMall\ProductRequest;
use App\Http\Controllers\Ablilty\Ablilty;
use App\Http\Controllers\Ablilty\ShoppingMallWindowInterface;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * @group Product API
 *
 * 상품 API입니다.
 */
class ProductController extends Controller
{
    use ManagerTrait, ExtendResponseTrait;
    protected $products, $merchandises;

    public function __construct(Product $products)
    {
        $this->products = $products;
        $this->target = '상품';
        $this->imgs = [
            'params'    => [
                'product_file',
            ],
            'cols'      => [
                'product_img',
            ],
            'folders'   => [
                'products',
            ],
            'sizes' => [
                1024,
            ],
        ];
    }
    
    public function initCache($pmod_id, $product_id)
    {
        $shop = $this->products
            ->join('payment_modules', 'products.pmod_id', '=', 'payment_modules.id')
            ->join('shopping_malls', 'payment_modules.mcht_id', '=', 'shopping_malls.mcht_id')
            ->where('payment_modules.id', $pmod_id)
            ->first();
        if($shop)
        {
            ShoppingMallWindowInterface::initShopInfo($shop->window_code);
            ShoppingMallWindowInterface::initProductInfo($product_id);
        }
    }

    /**
     * 목록출력
     *
     * 가맹점 이상 가능
     */
    public function index(IndexRequest $request)
    {
        $cols = [
            'categories.category_name', 'merchandises.mcht_name',
            'products.*',  'payment_modules.note as pmod_note', 
            'payment_modules.pay_window_secure_level',
        ];
        $search = $request->input('search', '');
        $query  = $this->products
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->join('merchandises', 'categories.mcht_id', '=', 'merchandises.id')
            ->join('payment_modules', 'products.pmod_id', '=', 'payment_modules.id')
            ->where('merchandises.brand_id', $request->user()->brand_id)
            ->where(function ($query) use ($search) {
                return $query->where('products.product_name', 'like', "%$search%")
                    ->orWhere('merchandises.mcht_name', 'like', "%$search%");
            })
            ->with(['productOptionGroups.productOptions']);

        if(Ablilty::isMerchandise($request))
            $query = $query->where('merchandises.id', $request->user()->id);

        $data   = $this->getIndexData($request, $query, 'products.id', $cols, 'products.created_at');
        return $this->response(0, $data);
    }

    /**
     * 추가
     *
     * 가맹점 이상 가능
     *
     */
    public function store(ProductRequest $request)
    {
        $data = $request->data();
        $data = $this->saveImages($request, $data, $this->imgs);
        $res = $this->products->create($data);
        if($res)
            $this->initCache($data['pmod_id'], $res->id);
        return $this->response($res ? 1 : 990, ['id'=>$res->id]);
    }

    /**
     * 단일조회
     *
     * 가맹점 이상 가능
     *
     * @urlParam id integer required 빌키 PK
     */
    public function show(Request $request, int $id)
    {
        $data = $this->products->where('id', $id)->with(['productOptionGroup.productOption'])->first();
        return $this->response($data ? 0 : 1000, $data);
    }

    /**
     * 업데이트
     *
     * 가맹점 이상 가능
     *
     * @urlParam id integer required 빌키 PK
     */
    public function update(ProductRequest $request, int $id)
    {
        $data = $request->data();
        $data = $this->saveImages($request, $data, $this->imgs);
        $res  = $this->products->where('id', $id)->update($data);
        if($res)
            $this->initCache($data['pmod_id'], $id);
        return $this->response($res ? 1 : 990);
    }

    /**
     * 단일삭제
     *
     * 가맹점
     * 
     * @urlParam id integer required 빌키 PK
     */
    public function destroy(Request $request, int $id)
    {
        $query = $this->products->where('id', $id);
        $product = (clone $query)->first();
        $res = (clone $query)->delete();
        if($res)
            $this->initCache($product->pmod_id, $id);
        return $this->response($res ? 1 : 990, ['id'=>$id]);
    }
}
