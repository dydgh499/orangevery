<?php

namespace App\Http\Controllers\Manager\Service;

use App\Models\Service\FinanceVan;
use App\Models\Service\PaymentGateway;
use App\Models\Service\PaymentSection;
use App\Models\Service\Classification;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Requests\Manager\PayGatewayRequest;
use App\Http\Requests\Manager\IndexRequest;

use App\Http\Controllers\Ablilty\Ablilty;
use App\Http\Controllers\Ablilty\EditAbleWorkTime;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * @group Payment Gateway API
 *
 * PG사 API 입니다. 본사 이상권한이 요구됩니다.
 */
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
     */
    public function store(PayGatewayRequest $request)
    {
        if(EditAbleWorkTime::validate() === false)
            return $this->extendResponse(1500, '지금은 작업할 수 없습니다.');

        $user = $request->data();
        $res = $this->pay_gateways->create($user);
        return $this->response($res ? 1 : 990, ['id'=>$res->id]);
    }

    /**
     * 단일조회
     *
     * 가맹점 이상 가능
     *
     * @urlParam id integer required PG사 id
     */
    public function show(Request $request, int $id)
    {
        $data = $this->pay_gateways->where('id', $id)->first();
        if($data)
        {
            if(Ablilty::isBrandCheck($request, $data->brand_id) === false)
                return $this->response(951);
            else
                return $this->response(0, $data) ;
        }
        else
            return $this->response(1000);
    }

    /**
     * 업데이트
     *
     * 가맹점 이상 가능
     *
     * @urlParam id integer required PG사 id
     */
    public function update(PayGatewayRequest $request, int $id)
    {
        $data = $this->pay_gateways->where('id', $id)->first();
        if(EditAbleWorkTime::validate() === false)
            return $this->extendResponse(1500, '지금은 작업할 수 없습니다.');
        if(Ablilty::isBrandCheck($request, $data->brand_id) === false)
                return $this->response(951);
        $data = $request->data();
        $res = $this->pay_gateways->where('id', $id)->update($data);
        return $this->response($res ? 1 : 990, ['id'=>$id]);
    }

    /**
     * 단일삭제
     *
     * @urlParam id integer required PG사 id
     */
    public function destroy(Request $request, int $id)
    {
        if($this->authCheck($request->user(), $id, 35))
        {
            $data = $this->pay_gateways->where('id', $id)->first();
            if(EditAbleWorkTime::validate() === false)
                return $this->extendResponse(1500, '지금은 작업할 수 없습니다.');
            if(Ablilty::isBrandCheck($request, $data->brand_id) === false)
                return $this->response(951);
            $res = $this->delete($this->pay_gateways->where('id', $id));
            return $this->response($res ? 1 : 990, ['id'=>$id]);
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

    /**
     * 매출전표 PG사 정보 API
     *
     * @urlParam id integer required PG사 id
     */
    public function saleSlip(Request $request, int $id)
    {
        $cols = ['id', 'pg_type' ,'company_name', 'business_num', 'rep_name', 'addr'];      
        $datas = $this->pay_gateways->where('id', $id)->get($cols);
        return $this->response(0, $datas);
    }
}
