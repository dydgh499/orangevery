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
use Illuminate\Http\Request;
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

    public function __construct(DifferenceSettlementHistory $difference_settlement_histories)
    {
        $this->difference_settlement_histories = $difference_settlement_histories; 
        $this->base_path = "App\Http\Controllers\Log\DifferenceSettlement\\";
    }

    public function commonSelect($request)
    {
        $query = TransactionFilter::common($request);
        $query  = TransactionFilter::date($request, $query);
        $query  = $query->join('difference_settlement_histories', 'transactions.id', '=', 'difference_settlement_histories.trans_id');
        if($request->status_code)
        {
            if((int)$request->status_code === 1)
                $query = $query->whereIn('difference_settlement_histories.settle_result_code', ['00', '0000']);
            else if((int)$request->status_code === 2)
                $query = $query->whereNotIn('difference_settlement_histories.settle_result_code', ['00', '0000', '50', '51']);
            else
                $query = $query->where('difference_settlement_histories.settle_result_code', $request->status_code);
        }
        if($request->only_appr)
            $query = $query->where('transactions.is_cancel', 0);
        return $query;

    }

    public function index(IndexRequest $request)
    {
        $query = $this->commonSelect($request);
        return $this->getIndexData($request, $query, 'difference_settlement_histories.id', [
            'difference_settlement_histories.*',
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
        ], 'transactions.trx_at');
    }

    /**
     * 차트 데이터 출력
     *
     * 운영자 이상 가능
     */
    public function chart(IndexRequest $request)
    {
        $query = $this->commonSelect($request);  
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

    /**
     * 차액정산 재시도
     *
     * 운영자 이상 가능
     */
    public function retry(Request $request)
    {
        $this->difference_settlement_histories
            ->whereIn('id', $request->selected)
            ->update([
                'settle_result_code' => '51',
                'settle_result_msg' => '재업로드 대기',
            ]);
            return $this->response(1, []);
    }
}
