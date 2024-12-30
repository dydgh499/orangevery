<?php

namespace App\Http\Controllers\Log;

use Carbon\Carbon;
use App\Models\Brand;
use App\Models\Transaction;
use App\Models\Merchandise;
use App\Models\Log\SubBusinessRegistration;
use App\Models\Log\DifferenceSettlementHistory;
use App\Http\Controllers\Manager\Transaction\TransactionFilter;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

use App\Http\Traits\StoresTrait;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Requests\Manager\IndexRequest;

/**
 * @group Difference-Settlement-History API
 *
 * 차액정산 이력 API 입니다.
 */
class DifferenceSettlementHistoryController extends Controller
{
    use ManagerTrait, ExtendResponseTrait, StoresTrait;
    protected $difference_settlement_histories;
    protected $base_path = "App\Http\Controllers\Log\DifferenceSettlement\\";
    protected $cols = [];

    public function __construct(DifferenceSettlementHistory $difference_settlement_histories)
    {
        $this->difference_settlement_histories = $difference_settlement_histories;    
        $this->base_path = "App\Http\Controllers\Log\DifferenceSettlement\\";
        $this->cols = [
            'difference_settlement_histories.*',
            'transactions.ord_num',
            'transactions.trx_id',
            'transactions.amount',
            'transactions.mid',
            'transactions.tid',
            'transactions.module_type',
            'transactions.installment',
            'transactions.pg_id',
            'transactions.ps_id',
            'transactions.ps_fee',
            'transactions.item_name',
            'transactions.card_num',
            'transactions.buyer_name',
            'transactions.buyer_phone',
            'transactions.custom_id',
            'transactions.terminal_id',
            'transactions.appr_num',
            'transactions.issuer',
            'transactions.acquirer',
            'transactions.mcht_settle_type',
            'transactions.trx_dt',
            'transactions.trx_tm',
            'transactions.cxl_dt',
            'transactions.cxl_tm',
            DB::raw("concat(transactions.trx_dt, ' ', transactions.trx_tm) AS trx_dttm"),
            DB::raw("concat(transactions.cxl_dt, ' ', transactions.cxl_tm) AS cxl_dttm"),
            'merchandises.mcht_name', 'merchandises.user_name', 'merchandises.nick_name',
            'merchandises.addr', 'merchandises.resident_num', 'merchandises.business_num',
        ];
    }

    public function index(IndexRequest $request)
    {
        $query = TransactionFilter::common($request);
        $query  = $query->join('difference_settlement_histories', 'transactions.id', '=', 'difference_settlement_histories.trans_id');
        return $this->getIndexData($request, $query, 'difference_settlement_histories.id', $this->cols, 'transactions.trx_at');
    }

    /**
     * 차트 데이터 출력
     *
     * 운영자 이상 가능
     */
    public function chart(IndexRequest $request)
    {
        $query  = TransactionFilter::common($request);        
        $query  = TransactionFilter::date($request, $query);
        $query  = $query->join('difference_settlement_histories', 'transactions.id', '=', 'difference_settlement_histories.trans_id');
        $chart  = $query->first([
            DB::raw("SUM(transactions.amount) AS amount"),
            DB::raw("SUM(difference_settlement_histories.supply_amount) AS supply_amount"),
            DB::raw("SUM(difference_settlement_histories.vat_amount) AS vat_amount"),
            DB::raw("SUM(difference_settlement_histories.settle_amount) AS settle_amount"),
        ]);
        $chart->amount          = (int)$chart->amount;
        $chart->supply_amount   = (int)$chart->supply_amount;
        $chart->vat_amount      = (int)$chart->vat_amount;
        $chart->settle_amount   = (int)$chart->settle_amount;
        return $this->response(0, $chart);
    }

    private function getUseDifferentSettlementBrands()
    {
        return Brand::join('different_settlement_infos', 'brands.id', '=', 'different_settlement_infos.brand_id')
            ->where('brands.is_delete', false)
            ->where('brands.use_different_settlement', true)
            ->where('different_settlement_infos.is_delete', false)
            ->get(['brands.business_num', 'different_settlement_infos.*']);
    }

    public function getPGClass($brand)
    {
        try
        {
            $pg_name = getPGType($brand->pg_type);
            $path   = $this->base_path.$pg_name;
            $pg     = new $path(json_decode(json_encode($brand), true));
        }
        catch(Exception $e)
        {   // pg사 발견못함
            $pg     = null;
            logging([
                    'message' => $e->getMessage(),
                    'brand' => json_decode(json_encode($brands[$i]), true),
                ],
                'PG사가 없습니다.'
            );
        }
        return $pg;
    }

    public function differenceSettleRequest()
    {
        $brands = $this->getUseDifferentSettlementBrands();
        $date       = Carbon::now();
        $yesterday  = $date->copy()->subDay(1)->format('Y-m-d');

        for ($i=0; $i<count($brands); $i++)
        {
            $trans = Transaction::join('merchandises', 'transactions.mcht_id', '=', 'merchandises.id')
                ->join('payment_gateways', 'transactions.pg_id', '=', 'payment_gateways.id')
                ->join('payment_modules', 'transactions.pmod_id', '=', 'payment_modules.id')
                ->where('merchandises.business_num', '!=', '')
                ->where('payment_gateways.pg_type', $brands[$i]->pg_type)
                ->where('transactions.brand_id', $brands[$i]->brand_id)
                ->where('transactions.trx_at', '>=', $yesterday." 00:00:00")
                ->where('transactions.trx_at', '<=', $yesterday." 23:59:59")
                ->get(['transactions.*', 'merchandises.business_num', 'payment_modules.p_mid']);
            $pg = $this->getPGClass($brands[$i]);
            if($pg)
            {
                $res = $pg->request($date, $trans);
            }
        }
    }

    public function differenceSettleResponse()
    {
        $brands = $this->getUseDifferentSettlementBrands();
        $date   = Carbon::now();

        for ($i=0; $i<count($brands); $i++)
        {
            $pg = $this->getPGClass($brands[$i]);
            if($pg)
            {
                $datas  = $pg->response($date);
                $res    = $this->manyInsert($this->difference_settlement_histories, $datas);
            }
        }
    }


    /*
    * 차액정산 가맹점 정보 등록
    */
    public function differenceSettleRegisterRequest()
    {
        $brands     = $this->getUseDifferentSettlementBrands();
        $date       = Carbon::now();

        for ($i=0; $i<count($brands); $i++)
        {
            $pg = $this->getPGClass($brands[$i]);
            if($pg)
            {
                $sub_business_regi_infos = SubBusinessRegistration::where('registration_result', -1)
                    ->where('brand_id', $brands[$i]->brand_id)
                    ->where('pg_type', $brands[$i]->pg_type)
                    ->orderby('business_num')
                    ->get();
                $cols = [
                    'merchandises.id', 'merchandises.sector', 'merchandises.business_num',
                    'merchandises.mcht_name','merchandises.addr',
                    'merchandises.nick_name','merchandises.phone_num',
                    'merchandises.email','merchandises.website_url',
                ];
                $query = Merchandise::where('merchandises.brand_id', $brands[$i]->brand_id)
                    ->whereIn('merchandises.business_num', $sub_business_regi_infos->pluck('business_num')->all())
                    ->where('merchandises.is_delete', false);
                if($brands[$i]->pg_type === 22)
                {
                    $query = $query->join('payment_modules', 'merchandises.id', '=', 'payment_modules.mcht_id')
                            ->where('payment_modules.p_mid', '!=', '');
                    $cols[] = 'payment_modules.p_mid';
                }

                $mchts = $query->orderby('merchandises.updated_at', 'desc')->get($cols);
                $res = $pg->registerRequest($date, $mchts, $sub_business_regi_infos);
                if($res)
                    SubBusinessRegistration::whereIn('id', $sub_business_regi_infos->pluck('id')->all())->update(['registration_result'=>-5]);                    
            }
        }
    }

    /*
    * 차액정산 가맹점 정보 등록 결과
    */
    public function differenceSettleResisterResponse()
    {
        $pay_gateways = $this->getUseDifferentSettlementPayGateways();
        $brand  = Brand::first(['business_num']);
        $date   = Carbon::now()->addDay(4);

        for ($i=0; $i<count($pay_gateways); $i++)
        {
            $pay_gateways[$i]->business_num = $brand->business_num;
            $pg = $this->getPGClass($pay_gateways[$i]);
            if($pg)
            {
                $datas  = $pg->registerResponse($date);
                foreach($datas as $data)
                {
                    if(isset($data['where']['id']))
                        SubBusinessRegistration::where('id', $data['where']['id'])->update($data['update']);
                    else
                    {
                        SubBusinessRegistration::where('pg_type', $data['pg_type'])
                            -where('business_num', $data['where']['business_num'])
                            -where('card_company_code', $data['where']['card_company_code'])
                            ->update($data['update']);
                    }
                }
            }
        }
    }

    /*
    * 차액정산 테스트 업로드
    */
    static public function differenceSettleRequestTest($ds_ids, $start_days, $end_days)
    {
        //DifferenceSettlementHistoryController::differenceSettleRequestTest([3,], 1)
        $date       = Carbon::now();
        $yesterday  = $date->copy()->subDay(1)->format('Y-m-d');
        $start_day  = $date->copy()->subDay($start_days)->format('Y-m-d');
        $end_day    = $date->copy()->subDay($end_days)->format('Y-m-d');
        foreach($ds_ids as $ds_id)
        {
            $brand = Brand::join('different_settlement_infos', 'brands.id', '=', 'different_settlement_infos.brand_id')
            ->where('brands.is_delete', false)
            ->where('different_settlement_infos.is_delete', false)
            ->where('brands.use_different_settlement', true)
            ->where('different_settlement_infos.id', $ds_id)
            ->first(['brands.business_num', 'different_settlement_infos.*']);
            if($brand)
            {
                $trans = Transaction::join('merchandises', 'transactions.mcht_id', '=', 'merchandises.id')
                    ->join('payment_gateways', 'transactions.pg_id', '=', 'payment_gateways.id')
                    ->join('payment_modules', 'transactions.pmod_id', '=', 'payment_modules.id')
                    ->where('merchandises.business_num', '!=', '')
                    ->where('payment_gateways.pg_type', $brand->pg_type)
                    ->where('transactions.brand_id', $brand->brand_id)
                    ->where(function ($query) {
                        return $query->where(function ($query) {
                            return $query->where('transactions.trx_at', '>=', $yesterday." 00:00:00")
                            ->where('transactions.trx_at', '<=', $yesterday." 23:59:59");
                        })->orWhere(function ($query) {
                            return $query->where('transactions.trx_at', '>=', $start_day." 00:00:00")
                            ->where('transactions.trx_at', '<=', $end_day." 23:59:59");
                        });
                    })
                    ->get(['transactions.*', 'merchandises.business_num', 'payment_modules.p_mid']);

                $inst = new DifferenceSettlementHistoryController(new DifferenceSettlementHistory);
                $pg = $inst->getPGClass($brand);
                if($pg)
                    $res = $pg->request($date, $trans);
            }
        }
    }

    static public function differenceSettleResponseTest($ds_id, $sub_day)
    {
        $date = Carbon::now()->subDay($sub_day);
        $brand = Brand::join('different_settlement_infos', 'brands.id', '=', 'different_settlement_infos.brand_id')
            ->where('brands.is_delete', false)
            ->where('different_settlement_infos.is_delete', false)
            ->where('brands.use_different_settlement', true)
            ->where('different_settlement_infos.id', $ds_id)
            ->first(['brands.business_num', 'different_settlement_infos.*']);       

        if($brand)
        {
            $inst = new DifferenceSettlementHistoryController(new DifferenceSettlementHistory);
            $pg = $inst->getPGClass($brand);
            if($pg)
            {
                $datas  = $pg->response($date);
                $res    = $inst->manyInsert($inst->difference_settlement_histories, $datas);
            }
        }
    }
}
