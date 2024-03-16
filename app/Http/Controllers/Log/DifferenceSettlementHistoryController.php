<?php

namespace App\Http\Controllers\Log;

use Carbon\Carbon;
use App\Models\Brand;
use App\Models\Transaction;
use App\Models\Merchandise;
use App\Models\Log\SubBusinessRegistration;
use App\Models\Log\DifferenceSettlementHistory;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

use App\Http\Traits\StoresTrait;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Traits\Settle\TransactionTrait;
use App\Http\Requests\Manager\IndexRequest;

/**
 * @group Difference-Settlement-History API
 *
 * 차액정산 이력 API 입니다.
 */
class DifferenceSettlementHistoryController extends Controller
{
    use ManagerTrait, ExtendResponseTrait, StoresTrait, TransactionTrait;
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
            DB::raw("concat(transactions.trx_dt, ' ', transactions.trx_tm) AS trx_dttm"),
            DB::raw("concat(transactions.cxl_dt, ' ', transactions.cxl_tm) AS cxl_dttm"),
            'merchandises.mcht_name', 'merchandises.user_name', 'merchandises.nick_name',
            'merchandises.addr', 'merchandises.resident_num', 'merchandises.business_num',
        ];
    }

    public function index(IndexRequest $request)
    {
        $query = $this->commonSelect($request);
        return $this->transPagenation($query, 'difference_settlement_histories', $this->cols, $request->page, $request->page_size);
    }

    public function commonSelect($request)
    {
        $search = $request->input('search', '');
        $query = $this->difference_settlement_histories
            ->join('transactions', 'difference_settlement_histories.trans_id', '=', 'transactions.id')
            ->join('merchandises', 'transactions.mcht_id', '=', 'merchandises.id')
            ->where('transactions.brand_id', $request->user()->brand_id)
            ->where('transactions.is_delete', false);

        if($search != '')
        {
            $query = $query->where(function ($query) use ($search) {
                return $query->where('transactions.mid', 'like', "%$search%")
                    ->orWhere('transactions.tid', 'like', "%$search%")
                    ->orWhere('transactions.trx_id', 'like', "%$search%")
                    ->orWhere('transactions.appr_num', 'like', "%$search%")
                    ->orWhere('merchandises.mcht_name', 'like', "%$search%")
                    ->orWhere('merchandises.resident_num', 'like', "%$search%")
                    ->orWhere('merchandises.business_num', 'like', "%$search%");
            });
        }
        $query = $this->transDateFilter($query, $request->s_dt, $request->e_dt, $request->use_search_date_detail);
        $query = globalPGFilter($query, $request, 'transactions');
        $query = globalSalesFilter($query, $request, 'transactions');
        $query = globalAuthFilter($query, $request, 'transactions');
        return $query;
    }

    /**
     * 차트 데이터 출력
     *
     * 운영자 이상 가능
     */
    public function chart(IndexRequest $request)
    {
        $query  = $this->commonSelect($request);
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
            ->where('different_settlement_infos.is_delete', false)
            ->where('brands.use_different_settlement', true)
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
                ->where('transactions.is_delete', false)
                ->where('merchandises.is_delete', false)
                ->where('merchandises.business_num', '!=', '')
                ->where('payment_gateways.pg_type', $brands[$i]->pg_type)
                ->where('transactions.brand_id', $brands[$i]->brand_id)
                ->where(function ($query) use ($yesterday) {
                    $query->where(function ($query) use ($yesterday) {                        
                        $query->where('transactions.trx_dt', $yesterday)
                            ->where('transactions.is_cancel', false);
                    })->orWhere(function ($query) use ($yesterday) {
                        $query->where('transactions.cxl_dt', $yesterday)
                        ->where('transactions.is_cancel', true);
                    });
                })
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

                $mchts = Merchandise::where('brand_id', $brands[$i]->brand_id)
                    ->where('is_delete', false)
                    ->whereIn('business_num', $sub_business_regi_infos->pluck('business_num')->all())
                    ->get([
                        'id', 'sector', 'business_num',
                        'mcht_name','addr',
                        'nick_name','phone_num',
                        'email','website_url',
                    ]);

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
        $brands = $this->getUseDifferentSettlementBrands();
        $date   = Carbon::now();

        for ($i=0; $i<count($brands); $i++)
        {
            $pg = $this->getPGClass($brands[$i]);
            if($pg)
            {
                $datas  = $pg->registerResponse($date);
                //$res  = $this->manyInsert($this->difference_settlement_histories, $datas);
            }
        }
    }

    static public function differenceSettleRequestTest()
    {
        $ds_id      = 1;
        $date       = Carbon::now();
        $yesterday  = $date->copy()->subDay(1)->format('Y-m-d');

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
                ->where('transactions.is_delete', false)
                ->where('merchandises.is_delete', false)
                ->where('merchandises.business_num', '!=', '')
                ->where('payment_gateways.pg_type', $brand->pg_type)
                ->where('transactions.brand_id', $brand->brand_id)
                ->where('transactions.trx_dt', '!=', '2024-03-06')
                ->where('transactions.trx_dt', '!=', '2024-03-15')
                ->where('transactions.cxl_dt', '!=', '2024-03-06')
                ->where('transactions.cxl_dt', '!=', '2024-03-15')
                ->where(function ($query) use ($yesterday) {
                    $query->where(function ($query) use ($yesterday) {                        
                        $query->where('transactions.trx_dt', '<=', $yesterday)
                            ->where('transactions.trx_dt', '>=', '2024-02-28')
                            ->where('transactions.is_cancel', false);
                    })->orWhere(function ($query) use ($yesterday) {
                        $query->where('transactions.cxl_dt', '<=', $yesterday)
                        ->where('transactions.cxl_dt', '>=', '2024-02-28')
                        ->where('transactions.is_cancel', true);
                    });
                })
                ->get(['transactions.*', 'merchandises.business_num', 'payment_modules.p_mid']);

            $ist = new DifferenceSettlementHistoryController(new DifferenceSettlementHistory);
            $pg = $ist->getPGClass($brand);
            if($pg)
                $res = $pg->request($date, $trans);
        }
    }

    static public function differenceSettleResponseTest()
    {
        $ds_id      = 1;
        $date       = Carbon::now()->subDay(15);

        $brand = Brand::join('different_settlement_infos', 'brands.id', '=', 'different_settlement_infos.brand_id')
            ->where('brands.is_delete', false)
            ->where('different_settlement_infos.is_delete', false)
            ->where('brands.use_different_settlement', true)
            ->where('different_settlement_infos.id', $ds_id)
            ->first(['brands.business_num', 'different_settlement_infos.*']);       

        if($brand)
        {
            $ist = new DifferenceSettlementHistoryController(new DifferenceSettlementHistory);
            $pg = $ist->getPGClass($brand);
            if($pg)
            {
                $datas  = $pg->response($date);
                $res    = $ist->manyInsert($ist->difference_settlement_histories, $datas);
            }
        }
    }
}
