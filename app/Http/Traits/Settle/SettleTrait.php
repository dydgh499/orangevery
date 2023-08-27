<?php

namespace App\Http\Traits\Settle;
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

    private function getExistTransUserIds($col, $target)
    {   //#date
        return Transaction::settleFilter($target)    
            ->globalFilter()
            ->settleTransaction()
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

    protected function getTargetInfo($level)
    {   //SettleHistoryTrait와 같은 함수 존재
        $idx = globalLevelByIndex($level);
        $target_id =  'sales'.$idx.'_id';
        $target_settle_id =  'sales'.$idx.'_settle_id';
        return [$target_id, $target_settle_id];
    }

    protected function partSettleCommonQuery($request, $target_id, $target_settle_id)
    {
        $cols = ['transactions.*'];
        [$settle_key, $group_key] = $this->getSettleCol($request);
        array_push($cols, $settle_key." AS profit");

        $search = $request->input('search', '');
        $query = Transaction::where($target_id, $request->id)
            ->globalFilter()
            ->settleFilter($target_settle_id)
            ->settleTransaction()
            ->where(function ($query) use ($search) {
                return $query->where('mid', 'like', "%$search%")
                    ->orWhere('tid', 'like', "%$search%")
                    ->orWhere('trx_id', 'like', "%$search%")
                    ->orWhere('appr_num', 'like', "%$search%");
            })
            ->with(['mcht']);

        $query = globalPGFilter($query, $request);
        $query = globalSalesFilter($query, $request);
        $query = globalAuthFilter($query, $request);

        $data = $this->getIndexData($request, $query, 'id', $cols);
        $sales_ids      = globalGetUniqueIdsBySalesIds($data['content']);
        $salesforces    = globalGetSalesByIds($sales_ids);
        $data['content'] = globalMappingSales($salesforces, $data['content']);

        foreach($data['content'] as $content) 
        {
            $content->mcht_name = $content->mcht['mcht_name'];
            $content->makeHidden(['mcht']);
        }
        return $data;
    }
}
