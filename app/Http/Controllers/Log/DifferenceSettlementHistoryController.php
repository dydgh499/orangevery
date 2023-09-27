<?php

namespace App\Http\Controllers\Log;

use Carbon\Carbon;
use App\Models\Brand;
use App\Models\Transaction;
use App\Models\Log\DifferenceSettlementHistory;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

use App\Http\Traits\StoresTrait;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Traits\Settle\TransactionTrait;

use App\Http\Requests\Manager\IndexRequest;

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
            'transactions.module_type',
            'transactions.installment',
            'transactions.pg_id',
            'transactions.ps_id',
            'transactions.item_name',
            'transactions.card_num',
            'transactions.buyer_name',
            'transactions.buyer_phone',
            'transactions.issuer',
            'transactions.acquirer',
            'transactions.mcht_settle_type',
            'transactions.terminal_id',
            DB::raw("concat(transactions.trx_dt, ' ', transactions.trx_tm) AS trx_dttm"),
            DB::raw("concat(transactions.cxl_dt, ' ', transactions.cxl_tm) AS cxl_dttm"),
            'merchandises.mcht_name', 'merchandises.user_name', 'merchandises.nick_name',
            'merchandises.addr', 'merchandises.resident_num', 'merchandises.business_num',
        ];
    }

    public function index(IndexRequest $request)
    {
        $page       = $request->input('page');
        $page_size  = $request->input('page_size');
        $query = $this->commonSelect($request);
        return $this->transPagenation($query, 'difference_settlement_histories', $this->cols, $page, $page_size);
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
        if($request->has('s_dt') && $request->has('e_dt'))
        {
            $query = $query->where(function($query) use($request) {
                $query->where(function($query) use($request) {
                    $query->where('transactions.is_cancel', false)
                        ->where('transactions.trx_dt', '>=', $request->s_dt)
                        ->where('transactions.trx_dt', '<=', $request->e_dt);
                })->orWhere(function($query) use($request) {
                    $query->where('transactions.is_cancel', true)
                        ->where('transactions.cxl_dt', '>=', $request->s_dt)
                        ->where('transactions.cxl_dt', '<=', $request->e_dt);
                });
            });
            $request->query->remove('s_dt');
            $request->query->remove('e_dt');
        }

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
        return $this->response(0, $chart);
    }

    private function getUseDifferentSettlementBrands()
    {
        return Brand::where('is_delete', false)
            ->where('is_use_different_settlement', true)
            ->get(['business_num', 'rep_mcht_id', 'id', 'above_pg_type']);
    }

    public function request()
    {
        $brands = $this->getUseDifferentSettlementBrands();
        $date       = Carbon::now();
        $yesterday  = $date->copy()->subDay(1)->format('Y-m-d');

        for ($i=0; $i<count($brands); $i++)
        {
            $pg_name = getPGType($brands[$i]->above_pg_type);
            $trans   = Transaction::join('merchandises', 'transactions.mcht_id', '=', 'merchandises.id')
                ->join('payment_gateways', 'transactions.pg_id', '=', 'payment_gateways.id')
                ->where('transactions.is_delete', false)
                ->where('merchandises.is_delete', false)
                ->where('payment_gateways.pg_type', $brands[$i]->above_pg_type)
                ->where('transactions.brand_id', $brands[$i]->id)
                ->where('transactions.trx_dt', $yesterday)
                ->get(['transactions.*', 'merchandises.business_num']);
            try
            {
                $path   = $this->base_path.$pg_name;            
                $pg     = new $path($brands[$i]->rep_mcht_id);
                $pg->request($date, $brands[$i]->business_num, $trans);    
            }
            catch(Exception $e)
            {   // pg사 발견못함
                logging([
                        'message' => $e->getMessage(),
                        'brand' => json_decode(json_encode($brands[$i]), true),
                    ],
                    'PG사가 없습니다.'
                );
            }
        }
    }

    public function response()
    {
        $brands = $this->getUseDifferentSettlementBrands();
        $date       = Carbon::now();
        for ($i=0; $i<count($brands); $i++)
        {
            $pg_name = getPGType($brands[$i]->above_pg_type);
            try
            {
                $path   = $this->base_path.$pg_name;
                $pg     = new $path($brands[$i]->rep_mcht_id);
                $datas  = $pg->response($date);
                $res = $this->manyInsert($this->difference_settlement_histories, $datas);
            }
            catch(Exception $e)
            {   // pg사 발견못함
                logging([
                        'message' => $e->getMessage(),
                        'brand' => json_decode(json_encode($brands[$i]), true),
                    ],
                    'PG사가 없습니다.'
                );
            }
        }
    }
}
