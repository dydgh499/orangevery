<?php

namespace App\Http\Traits\Settle;
use Illuminate\Support\Facades\DB;
use App\Models\Log\RealtimeSendHistory;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Carbon\Carbon;

trait SettleTrait
{
    public function getDefaultCols()
    {
        return [
            'id', 'user_name', 'addr', 'nick_name',
            'phone_num', 'sector', 'business_num', 'resident_num', 
            "acct_num", "acct_name", "acct_bank_name", "acct_bank_code",
        ];
    }
    public function getSettleInformation($data, $settle_amount)
    {
        foreach($data['content'] as $content) {
            $chart = getDefaultTransChartFormat($content->transactions, $settle_amount);
            $content->total = $chart['total'];
            $content->appr = $chart['appr'];
            $content->cxl = $chart['cxl'];
            $content->deduction = [
                'input' => null,
                'amount' => $content->deducts->sum('amount'),
            ];
            $content->terminal = [
                'amount' => 0,
                'under_sales_amount' => 0,
                'terminal_settle_count' => 0,
            ];
            $content->settle_transaction_idxs = $content->transactions->pluck('id');
        }
        return $data;
    }

    private function getDefaultQuery($query, $request, $ids)
    {   // 삭제된 가맹점, 영업점도 나와야함
        return $query
            ->where('brand_id', $request->user()->brand_id)
            ->whereIn('id', $ids);
    }

    private function getExistTransUserIds($col, $target)
    {   //#date groupby하면 속도에서 느려짐
        return Transaction::noSettlement($target)
            ->distinct()
            ->pluck($col)
            ->all();
    }

    private function commonDeduct($orm, $col, $request)
    {
        $validated = $request->validate(['amount'=>'required|integer', 'e_dt'=>'required|date', 'id'=>'required']);
        $res = $orm->create([
            'brand_id'  => $request->user()->brand_id,
            'amount'    => $request->amount * -1,
            'deduct_dt' => $request->e_dt,
            $col   => $request->id,
        ]);
        return $this->response(1);
    }

    protected function partSettleCommonQuery($request)
    {
        $search = $request->input('search', '');
        [$target_id, $target_settle_id, $target_settle_amount] = getTargetInfo($request->level);

        $query = Transaction::join('merchandises', 'transactions.mcht_id', '=', 'merchandises.id')
            ->noSettlement($target_settle_id)
            ->globalFilter();

        if($search !== "")
        {
            $query = $query->where(function ($query) use ($search) {
                return $query->where('transactions.mid', 'like', "%$search%")
                    ->orWhere('transactions.tid', 'like', "%$search%")
                    ->orWhere('transactions.trx_id', 'like', "%$search%")
                    ->orWhere('transactions.appr_num', 'like', "%$search%");
            });
        }
        if($request->id)
            $query = $query->where("transactions.".$target_id, $request->id);
        if($request->only_cancel)
            $query = $query->where('transactions.is_cancel', true);

        if((int)$request->level === 10)
        {   // 영업점 단계에서는 없음
            if((int)$request->use_realtime_deposit === 1)
            {   // 실패건은 제외하고 조회
                $fails = RealtimeSendHistory::onlyFailRealtime();
                if(count($fails))
                    $query = $query->whereNotIn('transactions.id', $fails);
            }
            else
                $query = $query->where('transactions.mcht_settle_type', '!=', -1);    
        }

        if($request->use_collect_withdraw)
        {   // 모아서 출금건만 가능
            $query = $query
                ->join('payment_modules', 'transactions.pmod_id', '=', 'payment_modules.id')
                ->where('merchandises.use_collect_withdraw', true)
                ->where('payment_modules.fin_trx_delay', -1);
        }
        return $query;
    }

    // 최종 정산금 세팅
    protected function setSettleFinalAmount($data, $cancel_deposits)
    {
        foreach($data['content'] as $content) 
        {
            $cancels = $cancel_deposits->filter(function($cancel_deposit) use($content) {  
                return $cancel_deposit->mcht_id === $content->id;
            });
            $cancel_deposit_amount = $cancels->sum('deposit_amount');
            $cancel_idxs = $cancels->pluck('id')->all();

            $settle = $content->total['profit'] + $content->deduction['amount'];
            $settle += $content->terminal['amount'];
            $settle += $content->terminal['under_sales_amount'];            
            $settle += $cancel_deposit_amount;
            $settle -= $content->withdraw_fee;

            $content->cancel_deposit_idxs = $cancel_idxs;
            $content->settle = [
                'cancel_deposit_amount'   => $cancel_deposit_amount,
                'withdraw_fee' => $content->withdraw_fee * -1,
                'amount'    => $settle,
                'deposit'   => $settle,
                'transfer'  => $settle,
            ];
            $content->makeHidden(['transactions']);
        }
        return $data;
    }

    // 취소 수기입금 건
    private function getCancelDeposits($s_dt, $e_dt, $data)
    {
        if(count($data['content']))
        {
            $ids = $data['content']->pluck('id')->all();
            return Transaction::join('cancel_deposits', 'transactions.id', '=', 'cancel_deposits.trans_id')
                ->whereNull('cancel_deposits.mcht_settle_id')
                ->where('cancel_deposits.settle_dt', '<=', Carbon::createFromFormat('Y-m-d', $e_dt)->format('Ymd'))
                ->where('cancel_deposits.settle_dt', '>=', Carbon::createFromFormat('Y-m-d', $s_dt)->format('Ymd'))
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

    /**
     * 부분정산 데이터 출력
     */
    public function part(Request $request)
    {
        $validated = $request->validate(['id'=>'required']);
        [$target_id, $target_settle_id, $target_settle_amount] = getTargetInfo($request->level);
        $cols = [
            'transactions.*', 
            DB::raw("concat(trx_dt, ' ', trx_tm) AS trx_dttm"),
            DB::raw("concat(cxl_dt, ' ', cxl_tm) AS cxl_dttm"),
            'merchandises.mcht_name',
            $target_settle_amount." AS profit",
        ];
        $query  = $this->partSettleCommonQuery($request);
        $data           = $this->getIndexData($request, $query, 'transactions.id', $cols, 'transactions.trx_at', false);
        $sales_ids      = globalGetUniqueIdsBySalesIds($data['content']);
        $salesforces    = globalGetSalesByIds($sales_ids);
        $data['content'] = globalMappingSales($salesforces, $data['content']);
        return $this->response(0, $data);
    }

    /**
     * 부분정산 차트 데이터 출력
     */
    public function partChart(Request $request)
    {
        $validated = $request->validate(['id'=>'required']);
        [$target_id, $target_settle_id, $target_settle_amount] = getTargetInfo($request->level);
        $cols  = $this->getTotalCols($target_settle_amount);
        $chart  = $this->partSettleCommonQuery($request)->first($cols);
        return $this->response(0, $this->setTransChartFormat($chart));
    }
}
