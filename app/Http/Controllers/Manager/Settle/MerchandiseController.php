<?php

namespace App\Http\Controllers\Manager\Settle;

use App\Models\Merchandise;
use App\Models\Transaction;
use App\Models\Log\RealtimeSendHistory;
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


    private function getCancelDeposits($request, $data)
    {
        if($request->use_cancel_deposit)
        {
            if(count($data['content']))
            {
                $ids = $data['content']->pluck('id')->all();
                return Transaction::join('cancel_deposits', 'transactions.id', '=', 'cancel_deposits.trans_id')
                    ->whereNull('cancel_deposits.mcht_settle_id')
                    ->where('cancel_deposits.deposit_date', '<=', $request->e_dt)
                    ->whereIn('transactions.mcht_id', $ids)
                    ->get([
                        'cancel_deposits.id',
                        'transactions.mcht_id',
                        'cancel_deposits.deposit_amount',
                    ]);
            }
            else
                return collect([]);
        }
        else
            return collect([]);
    }

    private function commonQuery($request)
    {
        $validated = $request->validate(['s_dt'=>'required|date', 'e_dt'=>'required|date']);
        [$settle_key, $group_key] = $this->getSettleCol($request);

        $cols = array_merge($this->getDefaultCols(), ['mcht_name', 'use_collect_withdraw', 'withdraw_fee']);
        if($request->input('use_settle_hold', 0))
            $cols = array_merge($cols, ['settle_hold_s_dt', 'settle_hold_reason']);

        // ----- 가맹점 목록 조회 ---------
        $mcht_ids = $this->getExistTransUserIds('mcht_id', 'mcht_settle_id');
        $terminal_settle_ids = $this->getTerminalSettleIds($request, 10, 'id');

        $query = $this->getDefaultQuery($this->merchandises, $request, $mcht_ids)
            ->where('mcht_name', 'like', "%".$request->search."%")
            ->orWhere(function ($query) use($terminal_settle_ids) {    
                $query->whereIn('id',$terminal_settle_ids);
            });

        // 모아서 출금
        if($request->use_collect_withdraw)
            $query = $query->with(['collectWithdraws']);

        $with = ['deducts', 'settlePayModules', 'transactions'];
        $data = $this->getIndexData($request, $query->with($with), 'id', $cols, "created_at", false);
        $data = $this->getSettleInformation($data, $settle_key);
        $data = $this->setTerminalCost($data, $request->s_dt, $request->s_dt, 'mcht_id');
        // set total settle
        $data = $this->setSettleFinalAmount($data, $this->getCancelDeposits($request, $data));
        return $data;
    }

    /**
     * 차트 데이터 출력
     *
     * 가맹점 이상 가능
     */
    public function chart(Request $request)
    {
        $total = [
            'id' => '합계',
            'deduction' => [
                'input' => null,
                'amount' => 0,
            ],
            'terminal' => [
                'amount' => 0,
                'under_sales_amount' => 0,
                'settle_pay_module_idxs' => 0,
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
        foreach($data['content'] as $item)
        {
            $transactions = $transactions->merge($item->transactions);
            $total['deduction']['amount'] += $item->deduction['amount'];
            $total['terminal']['amount'] += $item->terminal['amount'];
            $total['terminal']['under_sales_amount'] += $item->terminal['under_sales_amount'];
            $total['terminal']['settle_pay_module_idxs'] += count($item->terminal['settle_pay_module_idxs']);

            $total['settle']['cancel_deposit_amount'] += $item->settle['cancel_deposit_amount'];
            $total['settle']['collect_withdraw_amount'] += $item->settle['collect_withdraw_amount'];
            $total['settle']['amount'] += $item->settle['amount'];
            $total['settle']['deposit'] += $item->settle['deposit'];
            $total['settle']['transfer'] += $item->settle['transfer'];
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
        [$settle_key, $group_key] = $this->getSettleCol($request);
        $cols  = $this->getTotalCols($settle_key);
        $query = Transaction::where('mcht_id', $request->id)
            ->noSettlement('mcht_settle_id')
            ->where(function ($query) use ($search) {
                return $query->where('transactions.mid', 'like', "%$search%")
                    ->orWhere('transactions.tid', 'like', "%$search%")
                    ->orWhere('transactions.trx_id', 'like', "%$search%")
                    ->orWhere('transactions.appr_num', 'like', "%$search%");
            });
        if($request->only_cancel)
            $query = $query->where('transactions.is_cancel', true);

        // 실패건은 제외하고 조회
        if((int)$request->use_realtime_deposit === 1)
        {
            $fails = RealtimeSendHistory::onlyFailRealtime();
            if(count($fails))
                $query = $query->whereNotIn('transactions.id', $fails);
        }
        else
            $query = $query->where('transactions.mcht_settle_type', '!=', -1);
       //TODO: 영업점으로 통일할때 꼭 mcht_id if 적용

        $chart = $query->first($cols);
        if($chart)
        {
            $chart = $this->setTransChartFormat($chart);
            return $this->response(0, $chart);        
        }
        else
        {
            return $this->response(0, [
                'appr'  => [
                    'amount'=> 0,
                    'count' => 0,
                ],
                'cxl'   => [
                    'amount'=> 0,
                    'count' => 0,
                ],
                'amount'    => 0,
                'count'     => 0,
                'profit'    => 0,
            ]);
        }
    }
}
