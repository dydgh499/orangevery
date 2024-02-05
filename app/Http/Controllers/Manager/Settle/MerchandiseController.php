<?php

namespace App\Http\Controllers\Manager\Settle;

use App\Models\Merchandise;
use App\Models\Transaction;
use App\Models\Log\SettleDeductMerchandise;
use App\Models\PaymentModule;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;

use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Traits\Settle\SettleTrait;
use App\Http\Traits\Settle\SettleTerminalTrait;
use App\Http\Traits\Settle\TransactionTrait;
use App\Http\Requests\Manager\IndexRequest;
use Illuminate\Support\Facades\DB;

/**
 * @group Settle-Merchandise API
 *
 * 가맹점 정산 관리 API 입니다.
 */
class MerchandiseController extends Controller
{
    use ManagerTrait, ExtendResponseTrait, SettleTrait, SettleTerminalTrait, TransactionTrait;
    protected $merchandises, $settleDeducts;

    public function __construct(Merchandise $merchandises, SettleDeductMerchandise $settleDeducts)
    {
        $this->merchandises = $merchandises;
        $this->settleDeducts = $settleDeducts;
    }


    protected function getTerminalSettleIds($request, $level, $target_id)
    {
        $query = PaymentModule::terminalSettle($level)
            ->where('merchandises.mcht_name', 'like', "%".$request->search."%");
        return globalAuthFilter($query, $request, 'merchandises')->byTargetIds($target_id);
    }

    private function commonQuery($request)
    {
        $with = ['deducts'];
        $validated = $request->validate(['s_dt'=>'required|date', 'e_dt'=>'required|date']);
        [$settle_key, $group_key] = $this->getSettleCol($request);

        $cols = array_merge($this->getDefaultCols(), ['mcht_name', 'use_collect_withdraw', 'withdraw_fee']);
        $search = $request->input('search', '');
        // ----- 가맹점 목록 조회 ---------
        $mcht_ids = $this->getExistTransUserIds('mcht_id', 'mcht_settle_id');
        $terminal_settle_ids = $this->getTerminalSettleIds($request, 10, 'id');

        $query = $this->getDefaultQuery($this->merchandises, $request, $mcht_ids)
                ->where('mcht_name', 'like', "%$search%")
                ->orWhere(function ($query) use($terminal_settle_ids) {    
                    $query->whereIn('id',$terminal_settle_ids);
                });

        if($request->use_realtime_deposit == 0)
        {   // 즉시출금 제외
            $mcht_ids = $query->pluck('id')->all();
            $unuse_realtime_ids = PaymentModule::whereIn('mcht_id', $mcht_ids)
                ->where('use_realtime_deposit', false)
                ->pluck('mcht_id')->all();
            $query = $query->whereIn('id', $unuse_realtime_ids);
        }
        // 모아서 출금
        if($request->use_collect_withdraw)
            $query = $query->with(['collectWithdraws']);
        // 취소 입금
        if($request->use_cancel_deposit)
            $with[] = 'transactions.cancelDeposits';
        else
            $with[] = 'transactions';

        $data = $this->getIndexData($request, $query->with($with), 'id', $cols, "created_at", false);
        $data = $this->getSettleInformation($data, $settle_key);
        // set terminals
        $mcht_ids = collect($data['content'])->pluck('id')->all();
        if(count($mcht_ids) && $terminal_settle_ids)
        {
            $settle_s_day   = date('d', strtotime($request->s_dt));
            $settle_e_day   = date('d', strtotime($request->e_dt));
            $settle_month   = date('Ym', strtotime($request->e_dt)); //202310
            $pay_modules    = collect(
                PaymentModule::whereIn('mcht_id', $mcht_ids)
                ->terminalSettle(10, 'id')
                ->get(['payment_modules.*'])
            );
            $data = $this->setTerminalCost($data, $pay_modules, $request->s_dt, $request->s_dt, 'mcht_id');
        }
        // set total settle
        $data = $this->setSettleFinalAmount($data);
        return $data;
    }
    
    /**
     * 차트 데이터 출력
     *
     * 가맹점 이상 가능
     */
    public function chart(Request $request)
    {
        $request = $request->merge([
            'page' => 1,
            'page_size' => 999999,
        ]);
        $total = [
            'id' => '합계',
            'deduction' => [
                'input' => null,
                'amount' => 0,
            ],
            'terminal' => [
                'amount' => 0,
                'under_sales_amount' => 0,
            ],
            'settle' => [
                'cancel_deposit_amount' => 0,
                'collect_withdraw_amount' => 0,
                'amount' => 0,
                'deposit' => 0,
                'transfer' => 0,
            ]
        ];
        [$settle_key, $group_key] = $this->getSettleCol($request);

        $transactions = collect();
        $data = $this->commonQuery($request);
        foreach($data['content'] as $data)
        {
            $transactions = $transactions->merge($data->transactions);
            $total['deduction']['amount'] += $data->deduction['amount'];
            $total['terminal']['amount'] += $data->terminal['amount'];
            $total['terminal']['under_sales_amount'] += $data->terminal['under_sales_amount'];

            $total['settle']['cancel_deposit_amount'] += $data->settle['cancel_deposit_amount'];
            $total['settle']['collect_withdraw_amount'] += $data->settle['collect_withdraw_amount'];
            $total['settle']['amount'] += $data->settle['amount'];
            $total['settle']['deposit'] += $data->settle['deposit'];
            $total['settle']['transfer'] += $data->settle['transfer'];
        }
        $chart = getDefaultTransChartFormat($transactions, $settle_key);
        $total = array_merge($total, $chart);
        return $this->response(0, $total);
    }

    public function index(IndexRequest $request)
    {
        $data = $this->commonQuery($request);
        return $this->response(0, $data);
    }

    public function deduct(Request $request) 
    {
        return $this->commonDeduct($this->settleDeducts, 'mcht_id', $request);
    }

    public function part(Request $request)
    {
        $data = $this->partSettleCommonQuery($request, 'mcht_id', 'mcht_settle_id');
        return $this->response(0, $data);
    }

    public function partChart(Request $request)
    {
        $search = $request->input('search', '');
        $request = $request->merge([
            'page' => 1,
            'page_size' => 999999,
        ]);
        [$settle_key, $group_key] = $this->getSettleCol($request);
        $cols  = $this->getTotalCols($settle_key);
        $chart = Transaction::where('mcht_id', $request->id)
            ->noSettlement('mcht_settle_id')
            ->where(function ($query) use ($search) {
                return $query->where('transactions.mid', 'like', "%$search%")
                    ->orWhere('transactions.tid', 'like', "%$search%")
                    ->orWhere('transactions.trx_id', 'like', "%$search%")
                    ->orWhere('transactions.appr_num', 'like', "%$search%");
            })
            ->first($cols);
        $chart = $this->setTransChartFormat($chart);
        return $this->response(0, $chart);
    }
}
