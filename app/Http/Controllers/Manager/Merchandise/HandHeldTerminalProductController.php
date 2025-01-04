<?php

namespace App\Http\Controllers\Manager\Merchandise;

use App\Models\Merchandise\ShoppingMall\Product;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Traits\StoresTrait;

use App\Http\Requests\Manager\IndexRequest;
use App\Http\Requests\Manager\Merchandise\ShoppingMall\ProductRequest;
use App\Http\Controllers\Ablilty\Ablilty;
use App\Http\Controllers\Ablilty\ShoppingMallWindowInterface;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * @group Bill Key API
 *
 * 수기단말기 상품 API입니다.
 */
class HandHeldTerminalProductController extends Controller
{
    use ManagerTrait, ExtendResponseTrait;
    protected $products;

    public function __construct(Product $products)
    {
        $this->products = $products;
    }


    /**
     * 목록출력
     *
     * 가맹점 이상 가능
     */
    public function index(IndexRequest $request)
    {
        $data = [];
        return $this->response(0, $data);
    }

    /**
     * 추가
     *
     * 대리점 이상 가능
     *
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'mcht_id'=>'required',
            'product_name'=>'required',
        ]);
        $data = [
            'brand_id' => $request->user()->brand_id,
            'mcht_id' => $request->mcht_id,
            'product_name' => $request->product_name,            
        ];
        $res = $this->products->create($data);
        return $this->response($res ? 1 : 990, ['id'=>$res->id, 'mcht_id'=>$data['mcht_id']]);
    }

    /**
     * 단일조회
     *
     * 가맹점 이상 가능
     *
     * @urlParam id integer required 유저 PK
     */
    public function show(Request $request, int $id)
    {
        $data = $this->products->where('id', $id)->first();
        return $data ? $this->response(0, $data) : $this->response(1000);
    }

    /**
     * 업데이트
     *
     * 가맹점 이상 가능
     *
     * @urlParam id integer required 유저 PK
     */
    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'mcht_id'=>'required',
            'product_name'=>'required',
        ]);
        $data = [
            'mcht_id' => $request->mcht_id,
            'product_name' => $request->product_name,            
        ];
        $res = $this->products->where('id', $id)->update($data);
        return $this->response($res ? 1 : 990, ['id'=>$id, 'mcht_id'=>$data['mcht_id']]);
    }

    /**
     * 단일삭제
     *
     * @urlParam id integer required 유저 PK
     */
    public function destroy(Request $request, int $id)
    {
        $res = $this->products->where('id', $id)->delete();
        return $this->response($res ? 1 : 990, ['id'=>$id]);
    }
}
