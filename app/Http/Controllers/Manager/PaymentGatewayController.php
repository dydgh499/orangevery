<?php

namespace App\Http\Controllers\Manager;

use App\Models\FinanceVan;
use App\Models\PaymentGateway;
use App\Models\PaymentSection;
use App\Models\Classification;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Requests\Manager\PayGatewayRequest;
use App\Http\Requests\Manager\IndexRequest;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PaymentGatewayController extends Controller
{
    use ManagerTrait, ExtendResponseTrait;
    protected $pay_gateways, $pay_sections;

    public function __construct(PaymentGateway $pay_gateways, PaymentSection $pay_sections)
    {
        $this->pay_gateways = $pay_gateways;
        $this->pay_sections = $pay_sections;
        $this->imgs = [];
    }

    /**
     * 목록출력
     *
     * 가맹점 이상 가능
     *
     */
    public function index(IndexRequest $request)
    {
        $query = $this->pay_gateways
            ->where('is_delete', false)
            ->where('brand_id', $request->user()->brand_id);
        $data = $this->getIndexData($request, $query);
        return $this->response(0, $data);
    }

    /**
     * 추가
     *
     * 대리점 이상 가능
     *
     * @bodyParam user_pw string 유저 패스워드
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(PayGatewayRequest $request)
    {
        $user = $request->data();
        $res = $this->pay_gateways->create($user);
        return $this->response($res ? 1 : 990, ['id'=>$res->id]);
    }

    /**
     * 단일조회
     *
     * 가맹점 이상 가능
     *
     * @urlParam id integer required 유저 PK
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, $id)
    {
        $data = $this->pay_gateways->where('id', $id)->first();
        return $data ? $this->response(0, $data) : $this->response(1000);
    }

    /**
     * 업데이트
     *
     * 가맹점 이상 가능
     *
     * @urlParam id integer required 유저 PK
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(PayGatewayRequest $request, $id)
    {
        $data = $request->data();
        $res = $this->pay_gateways->where('id', $id)->update($data);
        return $this->response($res ? 1 : 990);
    }

    /**
     * 단일삭제
     *
     * @urlParam id integer required 유저 PK
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, $id)
    {
        if($this->authCheck($request->user(), $id, 35))
        {
            $res = $this->delete($this->pay_gateways->where('id', $id));
            return $this->response($res);
        }
        else
            return $this->response(951);
    }

    /**
     * 조회 API
     *
     */
    public function detail(Request $request)
    {
        $brand_id = $request->user()->brand_id;
        $grouped = Classification::where('brand_id', $brand_id)->where('is_delete', false)->get()->groupBy('type');
        $finance_vans = FinanceVan::where('brand_id', $brand_id)->where('is_delete', false)->get();
        $data = [
            'pay_gateways' => $this->pay_gateways->where('brand_id', $brand_id)->where('is_delete', false)->get(),
            'pay_sections' => $this->pay_sections->where('brand_id', $brand_id)->where('is_delete', false)->get(),
            'terminals'    => isset($grouped[0]) ? $grouped[0] : [],
            'custom_filters' => isset($grouped[1]) ? $grouped[1] : [],
            'finance_vans'  => isset($finance_vans) ? $finance_vans : [],
        ];
        return $this->response(0, $data);
    }

    public function saleSlip(Request $request, $id)
    {
        $cols = ['id', 'pg_type' ,'company_name', 'business_num', 'rep_name', 'addr'];      
        $datas = $this->pay_gateways->where('id', $id)->get($cols);
        return $this->response(0, $datas);
    }
}
