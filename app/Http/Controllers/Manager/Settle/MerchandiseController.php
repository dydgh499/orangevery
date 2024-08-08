<?php

namespace App\Http\Controllers\Manager\Settle;

use App\Models\Merchandise;
use App\Models\Transaction;
use App\Models\Log\RealtimeSendHistory;
use App\Models\Log\SettleDeductMerchandise;
use App\Models\Merchandise\PaymentModule;
use App\Models\Service\CMSTransaction;

use App\Http\Controllers\Manager\Settle\AddDeduct;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;

use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Traits\Settle\SettleTrait;
use App\Http\Traits\Settle\SettleTerminalTrait;
use App\Http\Controllers\Manager\Salesforce\UnderSalesforce;
use App\Http\Controllers\Utils\ChartFormat;


use App\Http\Requests\Manager\IndexRequest;
use Illuminate\Support\Facades\DB;

/**
 * @group Settle-Merchandise API
 *
 * 가맹점 정산 관리 API 입니다.
 */
class MerchandiseController extends Controller
{
    use ManagerTrait, ExtendResponseTrait, SettleTrait, SettleTerminalTrait;
    protected $merchandises, $settleDeducts;

    public function __construct(Merchandise $merchandises, SettleDeductMerchandise $settleDeducts)
    {
        $this->merchandises = $merchandises;
        $this->settleDeducts = $settleDeducts;
    }

    protected function getTerminalSettleIds($request, $level, $target_id)
    {
        $query = PaymentModule::terminalSettle($level)
            ->where('merchandises.mcht_name', 'like', "%".$request->search."%")
            ->where('merchandises.use_collect_withdraw', false);
            
        if($request->pg_id)
            $query = $query->where('payment_modules.pg_id', $request->pg_id);
        if($request->ps_id)
            $query = $query->where('payment_modules.ps_id', $request->ps_id);
        if($request->terminal_id)
            $query = $query->where('payment_modules.terminal_id', $request->terminal_id);
        if($request->mcht_settle_type !== null)
            $query = $query->where('payment_modules.settle_type', $request->mcht_settle_type);
        if($request->module_type !== null)
            $query = $query->where('payment_modules.module_type', $request->module_type);

        $query = globalSalesFilter($query, $request, 'merchandises');
        return globalAuthFilter($query, $request, 'merchandises')->byTargetIds($target_id);
    }

    private function commonQuery($request)
    {
        $validated = $request->validate(['s_dt'=>'required|date', 'e_dt'=>'required|date']);
        [$target_id, $target_settle_id, $target_settle_amount] = getTargetInfo($request->level);

        $cols = array_merge($this->getDefaultCols(), ['mcht_name', 'use_collect_withdraw', 'withdraw_fee']);
        $cols = UnderSalesforce::getViewableSalesCols($request, $cols);
        if($request->input('use_settle_hold', 0))
            $cols = array_merge($cols, ['settle_hold_s_dt', 'settle_hold_reason']);

        // ----- 가맹점 목록 조회 ---------
        $mcht_ids = $this->getExistTransUserIds($target_id, $target_settle_id);
        #$terminal_settle_ids = $this->getTerminalSettleIds($request, 10, 'id');

        $query = $this->getDefaultQuery($this->merchandises, $request, $mcht_ids)
            ->where('use_collect_withdraw', false)
            ->where('mcht_name', 'like', "%".$request->search."%");
            /*
            ->orWhere(function ($query) use($terminal_settle_ids) {    
                $query->whereIn('id',$terminal_settle_ids);
            });
            */
        // 모아서 출금
        if($request->mcht_id)
            $query = $query->where('id', $request->mcht_id);

        $with = ['deducts', 'settlePayModules', 'transactions'];
        $data = $this->getIndexData($request, $query->with($with), 'id', $cols, "created_at", false);
        $data = $this->getSettleInformation($data, $target_settle_amount);
        $data = $this->setTerminalCost($data, $request->s_dt, $request->e_dt, 'mcht_id');
        // set total settle
        $data = $this->setSettleFinalAmount($data, $this->getCancelDeposits($request->s_dt, $request->e_dt, $data));
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
                'amount' => 0,
                'deposit' => 0,
                'transfer' => 0,
            ]
        ];

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
            $total['settle']['amount'] += $item->settle['amount'];
            $total['settle']['deposit'] += $item->settle['deposit'];
            $total['settle']['transfer'] += $item->settle['transfer'];
        }
        [$target_id, $target_settle_id, $target_settle_amount] = getTargetInfo($request->level);
        $total = array_merge($total, ChartFormat::settle($transactions, $target_settle_amount));
        return $this->response(0, $total);
    }

    public function index(IndexRequest $request)
    {
        $data = $this->commonQuery($request);
        return $this->response(0, $data);
    }

    public function deduct(Request $request)
    {
        $code = AddDeduct::validate($request, 'mcht_id');
        if($code === -1)
            return $this->extendResponse(1999, '하루 추가차감 최대회수 10회를 초과하였습니다.');
        else if($code === -2)
            return $this->extendResponse(1999, '한 가맹점당 추가차감은 1번씩만 가능합니다.');
        else if($code === -3)
            return $this->extendResponse(1999, '추가차감은 300만원 이상할 수 없습니다.');
        else
            return $this->commonDeduct($this->settleDeducts, 'mcht_id', $request);
    }

    /*
    * 금일 입금액 검증
    */
    public function depositValidate(Request $request)
    {
        $res = json_decode($this->chart($request)->getContent(), true);
        $cms = CMSTransaction::where('brand_id', $request->user()->brand_id)
            ->where('trx_at', '>=', $request->s_dt." 00:00:00")
            ->where('trx_at', '<=', $request->e_dt." 23:59:59")
            ->where('result_code', '0000')
            ->whereIn('fin_id', $request->fin_ids)
            ->where('is_withdraw', false)
            ->first([
                DB::raw('SUM(amount) as deposit_amount')
            ]);
        $data = [
            'settle_amount' => $res['settle']['deposit'],
            'deposit_amount' => (int)$cms->deposit_amount,
            'profit_amount' => (int)$cms->deposit_amount - $res['settle']['deposit']
        ];
        return $this->response(0, $data);
    }
}
