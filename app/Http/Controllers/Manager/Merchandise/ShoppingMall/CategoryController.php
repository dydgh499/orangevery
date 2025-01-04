<?php

namespace App\Http\Controllers\Manager\Merchandise\ShoppingMall;

use App\Models\Merchandise\ShoppingMall\Category;
use App\Models\Merchandise\ShoppingMall\ShoppingMall;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Traits\StoresTrait;
use Illuminate\Support\Facades\DB;

use App\Http\Requests\Manager\IndexRequest;
use App\Http\Requests\Manager\Merchandise\ShoppingMall\CategoryRequest;
use App\Http\Controllers\Ablilty\Ablilty;
use App\Http\Controllers\Ablilty\ShoppingMallWindowInterface;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * @group Category Key API
 *
 * 카테고리 API입니다.
 */
class CategoryController extends Controller
{
    use ManagerTrait, ExtendResponseTrait, StoresTrait;
    protected $categories;

    public function __construct(Category $categories)
    {
        $this->categories = $categories;
        $this->target = '카테고리';
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
     */
    public function index(IndexRequest $request)
    {
        $cols = [
            'categories.*', 'merchandises.mcht_name', 
        ];
        $search = $request->input('search', '');
        $query  = $this->categories
            ->join('merchandises', 'categories.mcht_id', '=', 'merchandises.id')
            ->where('merchandises.brand_id', $request->user()->brand_id)
            ->where(function ($query) use ($search) {
                return $query->where('categories.category_name', 'like', "%$search%")
                    ->orWhere('merchandises.mcht_name', 'like', "%$search%");
            })
            ->with(['productCountGroup']);

        if(Ablilty::isMerchandise($request))
            $query = $query->where('merchandises.id', $request->user()->id);

        $data   = $this->getIndexData($request, $query, 'categories.id', $cols, 'categories.created_at');
        foreach($data['content'] as $content) 
        {
            $content->product_count = count($content->productCountGroup) ? $content->productCountGroup[0]->product_count : 0;
            $content->makeHidden(['productCountGroup']);
        }
        return $this->response(0, $data);
    }

    /**
     * 추가
     *
     * 가맹점 이상 가능
     *
     */
    public function store(CategoryRequest $request)
    {
        $data = $request->data();
        $data = $this->saveImages($request, $data, $this->imgs);
        $res  = $this->categories->create($data);
        $shop = ShoppingMall::where('mcht_id', $data['mcht_id'])->first();
        if($shop)
        {
            ShoppingMallWindowInterface::initShopInfo($shop->window_code);
        }
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
        $data = $this->categories->where('id', $id)->first();
        return $this->response($data ? 0 : 1000, $data);
    }

    /**
     * 업데이트
     *
     * 가맹점 이상 가능
     *
     * @urlParam id integer required 빌키 PK
     */
    public function update(CategoryRequest $request, int $id)
    {
        $data = $request->data();
        $data = $this->saveImages($request, $data, $this->imgs);
        $result  = $this->categories->where('id', $id)->update($data);

        $shop = ShoppingMall::where('mcht_id', $data['mcht_id'])->first();
        if($shop)
        {
            ShoppingMallWindowInterface::initShopInfo($shop->window_code);
        }
        return $this->response($result ? 1 : 990);
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
        $res = $this->categories->where('id', $id)->delete();
        return $this->response($res ? 1 : 990, ['id'=>$id]);
    }
}
