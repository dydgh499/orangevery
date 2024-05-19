<?php

namespace App\Http\Controllers\Manager\Settle;

use App\Models\Merchandise;
use App\Models\Transaction;
use App\Models\Log\RealtimeSendHistory;
use App\Models\Log\SettleDeductMerchandise;
use App\Models\Merchandise\PaymentModule;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;

use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Traits\Settle\SettleTrait;
use App\Http\Traits\Settle\SettleTerminalTrait;
use App\Http\Traits\Settle\TransactionTrait;
use App\Http\Traits\Salesforce\UnderSalesTrait;

use App\Http\Requests\Manager\IndexRequest;
use Illuminate\Support\Facades\DB;

/**
 * @group Rep-Settle-Merchandise API
 *
 * 대표가맹점 정산 관리 API 입니다.
 */
class RepMerchandiseController extends Controller
{
    use ManagerTrait, ExtendResponseTrait, SettleTrait, SettleTerminalTrait, TransactionTrait, UnderSalesTrait;
    protected $merchandises, $settleDeducts;

    public function __construct(Merchandise $merchandises, SettleDeductMerchandise $settleDeducts)
    {
        $this->merchandises = $merchandises;
        $this->settleDeducts = $settleDeducts;
    }

    // 대표가맹점 ID 조회
    private function getRepMerchandiseIds($request, $brand_id)
    {
        $acct_nums = $this->merchandises
            ->where('is_delete', 0)
            ->where('brand_id', $brand_id)
            ->groupby('acct_num')
            ->havingRaw('COUNT(acct_num) > 1')
            ->pluck('acct_num')
            ->all();
        $mcht_ids = $this->merchandises
            ->where('is_delete', 0)
            ->where('brand_id', $brand_id)
            ->whereIn('acct_num', $acct_nums)
            ->pluck('id')
            ->all();
            
        return Transaction::noSettlement('mcht_settle_id')
                ->whereIn('mcht_id', $mcht_ids)
                ->distinct()
                ->pluck('mcht_id')
                ->all();
    }

    private function getCancelDeposits($request, $data)
    {
        if(count($data['content']))
        {
            $ids = $data['content']->pluck('id')->all();
            return Transaction::join('cancel_deposits', 'transactions.id', '=', 'cancel_deposits.trans_id')
                ->whereNull('cancel_deposits.mcht_settle_id')
                ->where('cancel_deposits.settle_dt', '<=', Carbon::createFromFormat('Y-m-d', $request->e_dt)->format('Ymd'))
                ->where('cancel_deposits.settle_dt', '>=', Carbon::createFromFormat('Y-m-d', $request->s_dt)->format('Ymd'))
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

    // 대표가맹점 리스트 조회
    private function getRepMerchandiseContent($request)
    {
        [$target_id, $target_settle_id, $target_settle_amount] = getTargetInfo(10);

        $cols = array_merge($this->getDefaultCols(), ['mcht_name', 'use_collect_withdraw', 'withdraw_fee']);
        $cols = $this->getViewableSalesCols($request, $cols);

        $mcht_ids = $this->getRepMerchandiseIds($request, $request->user()->brand_id);
        $query = $this->getDefaultQuery($this->merchandises, $request, $mcht_ids)
            ->where('use_collect_withdraw', false)
            ->where('mcht_name', 'like', "%".$request->search."%")
            ->with(['deducts', 'settlePayModules', 'transactions'])
            ->orderby('acct_num');
    
        $data = $this->getIndexData($request, $query, 'id', $cols, "created_at", false);
        $data = $this->getSettleInformation($data, $target_settle_amount);
        $data = $this->setTerminalCost($data, $request->s_dt, $request->s_dt, 'mcht_id');
        return $this->setSettleFinalAmount($data, $this->getCancelDeposits($request, $data));
    }

    // 계좌번호 별로 그룹화
    private function splitGroupAcctNum($data)
    {
        $acct_num_groups = [];

    }

    public function index(Request $request)
    {
        $validated = $request->validate(['s_dt'=>'required|date', 'e_dt'=>'required|date']);
        $data = $this->getRepMerchandiseContent($request);
        return $this->response(0, $data);
    }
}
