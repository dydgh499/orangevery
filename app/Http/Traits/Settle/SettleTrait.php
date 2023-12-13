<?php

namespace App\Http\Traits\Settle;
use Illuminate\Support\Facades\DB;
use App\Models\Transaction;

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

    public function getSettleInformation($data, $settle_key)
    {
        foreach($data['content'] as $content) {
            $chart = getDefaultTransChartFormat($content->transactions, $settle_key);
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
            ];
        }
        return $data;
    }

    private function getDefaultQuery($query, $request, $ids)
    {
        return $query
            ->where('brand_id', $request->user()->brand_id)
            ->where('is_delete', false)
            ->whereIn('id', $ids);
    }

    private function getExistTransUserIds($col, $target)
    {   //#date
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

    protected function getTargetInfo($level)
    {   //SettleHistoryTrait와 같은 함수 존재
        $idx = globalLevelByIndex($level);
        $target_id =  'sales'.$idx.'_id';
        $target_settle_id =  'sales'.$idx.'_settle_id';
        return [$target_id, $target_settle_id];
    }

    protected function partSettleCommonQuery($request, $target_id, $target_settle_id)
    {
        $search = $request->input('search', '');
        $cols = [
            'transactions.*', 
            DB::raw("concat(trx_dt, ' ', trx_tm) AS trx_dttm"),
            DB::raw("concat(cxl_dt, ' ', cxl_tm) AS cxl_dttm"),
            'merchandises.mcht_name',
        ];
        [$settle_key, $group_key] = $this->getSettleCol($request);
        array_push($cols, $settle_key." AS profit");

        $query = Transaction::join('merchandises', 'transactions.mcht_id', '=', 'merchandises.id')
            ->where("transactions.".$target_id, $request->id)
            ->noSettlement($target_settle_id)
            ->where(function ($query) use ($search) {
                return $query->where('transactions.mid', 'like', "%$search%")
                    ->orWhere('transactions.tid', 'like', "%$search%")
                    ->orWhere('transactions.trx_id', 'like', "%$search%")
                    ->orWhere('transactions.appr_num', 'like', "%$search%");
            });
            
        if($request->only_cancel)
            $query = $query->where('transactions.is_cancel', true);
        if($request->use_collect_withdraw)
        {
            // 모아서 출금건만 가능
            $query = $query
                ->join('payment_modules', 'transactions.pmod_id', '=', 'payment_modules.id')
                ->where('merchandises.use_collect_withdraw', true)
                ->where('payment_modules.fin_trx_delay', -1);
        }
        $data = $this->transPagenation($query, 'transactions', $cols, $request->page, $request->page_size);
        return $data;
    }

    // 최종 정산금 세팅
    protected function setSettleFinalAmount($data)
    {
        foreach($data['content'] as $content) 
        {
            $settle = $content->total['profit'] + $content->deduction['amount'];
            $settle += $content->terminal['amount'];
            $settle += $content->terminal['under_sales_amount'];

            $cancel_deposit = request()->use_cancel_deposit ? $content->transactions->reduce(function($carry, $transaction) {
                return $carry + $transaction->cancelDeposits->sum('deposit_amount');
            }, 0) : 0;

            $total_withdraw_amount = request()->use_collect_withdraw ? $content->collectWithdraws->sum('total_withdraw_amount') : 0;
            $settle += $cancel_deposit;
            $settle -= $total_withdraw_amount;
            $settle -= $content->withdraw_fee;
            $content->settle = [
                'cancel_deposit_amount'   => $cancel_deposit,
                'collect_withdraw_amount' => $total_withdraw_amount * -1,
                'withdraw_fee' => $content->withdraw_fee * -1,
                'amount'    => $settle,
                'deposit'   => $settle,
                'transfer'  => $settle,
            ];
            $content->makeHidden(['transactions', 'collectWithdraws']);
        }
        return $data;
    }
}
