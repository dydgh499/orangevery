<?php

namespace App\Http\Controllers\Manager\Service;

use App\Models\Service\FinanceVan;
use App\Models\Service\PaymentGateway;
use App\Models\Service\PaymentSection;
use App\Models\Pay\PaymentModule;
use App\Models\Pay\BillKey;

use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Requests\Manager\Service\PayGatewayRequest;
use App\Http\Requests\Manager\IndexRequest;

use App\Http\Controllers\Ablilty\Ablilty;
use App\Http\Controllers\Ablilty\EditAbleWorkTime;
use App\Http\Controllers\Ablilty\ActivityHistoryInterface;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * @group Payment Gateway API
 *
 * 결제대행사 API 입니다. 본사 이상권한이 요구됩니다.
 */
class PaymentGatewayController extends Controller
{
    use ManagerTrait, ExtendResponseTrait;
    protected $pay_gateways;
    protected $target;

    public function __construct(PaymentGateway $pay_gateways)
    {
        $this->pay_gateways = $pay_gateways;
        $this->target       = '결제대행사';
    }

    /**
     * 목록출력
     *
     * 가맹점 이상 가능
     *
     */
    public function index(IndexRequest $request)
    {
        $query = $this->pay_gateways->where('is_delete', false);
        $query = brandFilter($query, $request);
        return $this->response(0, $query->get());
    }

    /**
     * 추가
     *
     * 대리점 이상 가능
     */
    public function store(PayGatewayRequest $request)
    {
        $data   = $request->data();
        $data['oper_id'] = $request->user()->id;
        if(EditAbleWorkTime::validate() === false)
            return $this->extendResponse(1500, '지금은 작업할 수 없습니다.');

        $res = app(ActivityHistoryInterface::class)->add($this->target, $this->pay_gateways, $data, 'pg_name');
        if($res)
            return $this->response(1, ['id'=>$res->id]);
        else
            return $this->response(990);
    }

    /**
     * 단일조회
     *
     * 가맹점 이상 가능
     *
     * @urlParam id integer required 결제대행사 id
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
     * @urlParam id integer required 결제대행사 id
     */
    public function update(PayGatewayRequest $request, int $id)
    {
        $query  = $this->pay_gateways->where('id', $id);
        $pg     = $query->first();
        $data   = $request->data();
        if(EditAbleWorkTime::validate() === false)
            return $this->extendResponse(1500, '지금은 작업할 수 없습니다.');
        if(Ablilty::isBrandCheck($request, $pg->brand_id) === false)
                return $this->response(951);
        $row = app(ActivityHistoryInterface::class)->update($this->target, $query, $data, 'pg_name');
        if($row)
            return $this->response(1, ['id' => $id]);
        else
            return $this->response(990);
    }

    /**
     * 단일삭제
     *
     * @urlParam id integer required 결제대행사 id
     */
    public function destroy(Request $request, int $id)
    {
        $query  = $this->pay_gateways->where('id', $id);
        $pg     = $query->first();
        if(EditAbleWorkTime::validate() === false)
            return $this->extendResponse(1500, '지금은 작업할 수 없습니다.');
        if(Ablilty::isBrandCheck($request, $pg->brand_id) === false)
            return $this->response(951);

        $row = app(ActivityHistoryInterface::class)->destory($this->target, $query, 'pg_name');
        return $this->response(1, ['id' => $id]);
    }

    /**
     * 조회 API
     *
     */
    public function detail(Request $request)
    {
        $bill_query = BillKey::join('payment_modules', 'bill_keys.pmod_id', '=', 'payment_modules.id')
            ->where('payment_modules.is_delete', false);
        $finance_vans   = FinanceVan::where('is_delete', false)->delivery()->get([
            'id', 'finance_company_num', 'balance_status', 'nick_name'
        ]);
        $pay_gateways   = brandFilter($this->pay_gateways->where('is_delete', false), $request)->get();
        $sections       = brandFilter(PaymentSection::where('is_delete', false), $request)->get();
        $pay_modules    = brandFilter(PaymentModule::where('is_delete', false), $request)->get(['id', 'note', 'module_type']);
        $bill_keys      = brandFilter($bill_query, $request)->get([
                'bill_keys.pmod_id',
                'bill_keys.id',
                'bill_keys.nick_name',
                'bill_keys.card_num',
        ]);
        $data = [
            'pay_gateways' => $pay_gateways,
            'pay_sections' => $sections,
            'finance_vans' => $finance_vans,
            'pay_modules'  => $pay_modules,
            'bill_keys'    => $bill_keys,
        ];
        return $this->response(0, $data);
    }
}
