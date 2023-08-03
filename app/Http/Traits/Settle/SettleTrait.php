<?php

namespace App\Http\Traits\Settle;
use App\Models\Transaction;

trait SettleTrait
{
    public function getDefaultCols()
    {
        return [
            'id', 'user_name', 'addr',
            'phone_num', 'sector', 'business_num', 'resident_num',
            "acct_num", "acct_name", "acct_bank_name", "acct_bank_code",
        ];
    }

    public function getSettleInformation($data)
    {
        foreach($data['content'] as $content) {
            $chart = getDefaultTransChartFormat($content->transactions);
            $content->amount = $chart['amount'];
            $content->count = $chart['count'];
            $content->profit = $chart['profit'];
            $content->trx_amount = $chart['trx_amount'];
            $content->hold_amount = $chart['hold_amount'];
            $content->settle_fee = $chart['settle_fee'];
            $content->total_trx_amount = $chart['total_trx_amount'];
            $content->appr = $chart['appr'];
            $content->cxl = $chart['cxl'];
            $content->deduction = [
                'input' => null,
                'amount' => $content->deducts->sum('amount'),
            ];
            $content->terminal = [
                'amount' => 0,   
            ];
            $content->settle = [
                'amount'    => $chart['profit'] + $content->deduction['amount'],
                'deposit'   => $chart['profit'] + $content->deduction['amount'],
                'transfer'  => $chart['profit'] + $content->deduction['amount'],
            ];
            $content->makeHidden(['transactions']);
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

    private function getExistTransUserIds($date, $col)
    {
        return Transaction::settleFilter()
            ->settleTransaction($date)
            ->distinct()
            ->get([$col])
            ->pluck([$col]);
    }

    private function commonDeduct($orm, $col, $request)
    {
        $validated = $request->validate(['amount'=>'required|integer', 'dt'=>'required|date', 'id'=>'required']);
        $res = $orm->create([
            'brand_id'  => $request->user()->brand_id,
            'amount'    => $request->amount * -1,
            'deduct_dt' => $request->dt,
            $col   => $request->id,
        ]);
        return $this->response(1);
    }
}
