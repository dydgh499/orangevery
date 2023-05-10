<?php

namespace App\Http\Controllers\Manager;

use App\Models\Order;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Requests\Manager\OrderForm;
use App\Http\Requests\Manager\IndexForm;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * @group Order API
 *
 * 주문내역 메뉴에서 사용될 API 입니다. 조회를 제외하고 마스터 이상권한이 요구됩니다.
 */
class OrderController extends Controller
{
    use ManagerTrait, ExtendResponseTrait;
    protected $orders;

    public function __construct(Order $orders)
    {
        $this->orders = $orders;
    }

    /**
     * 목록출력
     *
     * 가맹점 이상 가능
     *
     * @queryParam search string 검색어(상품명)
     */
    public function index(IndexForm $request)
    {
        $search = $request->input('search', '');
        $query  = $this->orders
            ->join('users', 'orders.user_id', '=', 'users.id')
            ->where('users.brand_id', $request->user()->brand_id)
            ->where('orders.prod_nm', 'like', "%$search%");

        $data   = $this->getIndexData($request, $query, 'orders.id', ['orders.*', 'users.user_name'], 'orders.created_at');
        return $this->response(0, $data);
    }

    /**
     * 추가
     *
     * 마스터 이상 가능
     *
     * @bodyParam user_id integer required 상위 상품 PK
     * @bodyParam mcht_id integer required 옵션 그룹 PK
     * @bodyParam prod_nm string required 옵션명
     * @bodyParam prod_amt string required 상품가격
     * @bodyParam trade_amt string required 거래가격
     * @bodyParam card_nm string required 카드사명
     * @bodyParam card_num string required 카드번호
     * @bodyParam instmt string required 할부기간
     * @bodyParam status string required 주문상태(0=주문처리중, 5=배달중, 10=주문처리완료, 15=주문취소, 20=환불)
     * @return \Illuminate\Http\Response
     */
    public function store(OrderForm $request)
    {
        $data = $request->data();
        $result = $this->orders->create($data);
        return $this->response($result ? 1 : 990);
    }

    /**
     * 단일조회
     *
     * 가맹점 이상 가능
     *
     * @urlParam id integer required 옵션 PK
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $data = $this->orders->where('id', $id)->first();
        return $this->response($data ? 0 : 1000, $data);
    }

    /**
     * 업데이트
     *
     * 마스터 이상 가능
     *
     * @urlParam id integer required 주문 PK
     * @bodyParam user_id integer required 구매유저 PK
     * @bodyParam mcht_id integer required 가맹점 PK
     * @bodyParam prod_nm string required 상품명
     * @bodyParam prod_amt string required 상품가격
     * @bodyParam trade_amt string required 거래가격
     * @bodyParam card_nm string required 카드사명
     * @bodyParam card_num string required 카드번호
     * @bodyParam instmt string required 할부기간
     * @bodyParam status string required 주문상태(0=주문처리중, 5=배달중, 10=주문처리완료, 15=주문취소, 20=환불)
     * @return \Illuminate\Http\Response
     */
    public function update(OrderForm $request, $id)
    {
        $data = $request->data();
        $result = $this->orders->where('id', $id)->update($data);
        return $this->response($result ? 1 : 990);
    }

    /**
     * 단일삭제
     *
     * 마스터 이상 가능
     *
     * @urlParam id integer required 주문 PK
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $result = $this->delete($this->orders->where('id', $id), []);
        return $this->response($result);
    }
}
