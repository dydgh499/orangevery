<?php
namespace App\Http\Controllers\Log\DifferenceSettlement;

use App\Http\Controllers\Log\DifferenceSettlement\MerchandiseRegistrationBatch;

use Carbon\Carbon;
use App\Models\Transaction;
use App\Models\Log\DifferenceSettlementHistory;
use Illuminate\Support\Facades\DB;

class DifferenceSettlementBatch extends MerchandiseRegistrationBatch
{
    // 차액 요청 거래 ID 수집
    public function getRequestTransIds($brand, $start_day, $end_day)
    {
        // 성공 거래건
        $new_ids    = Transaction::join('payment_gateways', 'transactions.pg_id', '=', 'payment_gateways.id')
            ->where('payment_gateways.pg_type', $brand->pg_type)
            ->where('transactions.trx_at', '>=', $start_day." 00:00:00")
            ->where('transactions.trx_at', '<=', $end_day." 23:59:59")
            ->pluck('transactions.id')
            ->all();

        // 재시도 거래건
        $start_of_year  = Carbon::now()->startOfYear()->toDateTimeString();
        $retry_ids  = Transaction::join('payment_gateways', 'transactions.pg_id', '=', 'payment_gateways.id')
            ->join('difference_settlement_histories', 'transactions.id', '=', 'difference_settlement_histories.trans_id')
            ->where('payment_gateways.pg_type', $brand->pg_type)
            ->where('transactions.trx_at', '>=', $start_of_year)   // index
            ->where('difference_settlement_histories.settle_result_code', '51')
            ->pluck('transactions.id')
            ->all();

        return array_unique(
            array_merge(
                $new_ids, 
                $retry_ids
            )
        );
    }

    // 차액 요청 거래 ID 중복 필터링
    public function filterDuplicateTransIds($full_histories)
    {
        $exist_trans_ids = [];
        foreach (array_chunk(array_column($full_histories, 'trans_id'), 3000) as $chunk) {
            $exist_trans_ids = array_merge($exist_trans_ids, DifferenceSettlementHistory::whereIn('trans_id', $chunk)
                ->pluck('trans_id')
                ->all());
        }
        if(count($exist_trans_ids))
        {
            $filtered_histories = array_values(array_filter($full_histories, function ($history) use ($exist_trans_ids) {
                return in_array($history['trans_id'], $exist_trans_ids) === false;
            }));
            error([
                'filtered_before' => count($exist_trans_ids), 'filtered_after' => count($filtered_histories)
            ],'exist already uploaded transactions');
            return $filtered_histories;
        }
        else
            return $full_histories;
    }

    // 차액 요청 거래 ID 중복 필터링
    public function differenceSettlementRequestProcess($brand, $date, $ids)
    {
        $results = collect();
        foreach(array_chunk($ids, 1000) as $chunk)
        {
            $partial_results = Transaction::join('merchandises', 'transactions.mcht_id', '=', 'merchandises.id')
                ->join('payment_modules', 'transactions.pmod_id', '=', 'payment_modules.id')
                ->whereIn('transactions.id', $chunk)
                ->where('payment_modules.is_different_settlement', 1)
                ->get([
                    'transactions.id', 'transactions.ord_num', 
                    'transactions.is_cancel', 'transactions.cxl_seq', 
                    'transactions.cxl_dt', 'transactions.trx_dt', 
                    'transactions.trx_at',  // nice
                    'transactions.trx_id', 'transactions.ori_trx_id', 
                    'transactions.mid', 'transactions.amount', 
                    'merchandises.business_num', 'payment_modules.p_mid', 
                ]);
            $results = $results->concat($partial_results);
        }
        $pg = $this->getPGClass($brand);
        if($pg)
        {   
            $full_histories = $this->filterDuplicateTransIds($pg->request($date, $results));
            $res = $this->manyInsert(new DifferenceSettlementHistory, $full_histories);
        }
    }

    public function getResponseSuccessFormat($exist_trans, $new_trans)
    {
        $datas = [];
        $items_by_trans_id = [];
        // 성능향상 O(1)
        foreach ($new_trans as $item) 
        {
            $items_by_trans_id[$item['trans_id']] = $item;
        }
        foreach ($exist_trans as $exist_tran)
        {
            if (isset($items_by_trans_id[$exist_tran->trans_id])) 
            {
                $item = $items_by_trans_id[$exist_tran->trans_id];
                $datas[] = [
                    'trans_id'           => $item['trans_id'],
                    'settle_result_code' => $item['settle_result_code'],
                    'settle_result_msg'  => $item['settle_result_msg'],
                    'updated_at'         => $item['updated_at'],
                    'mcht_section_code'  => $item['mcht_section_code'],
                    'req_dt'             => $exist_tran->req_dt,
                    'settle_dt'          => $item['settle_dt'],
                    'supply_amount'      => $item['supply_amount'],
                    'vat_amount'         => $item['vat_amount'],
                    'settle_amount'      => $item['settle_amount'],
                    'created_at'         => $exist_tran->created_at,
                    'updated_at'         => $item['updated_at'],
                ];
            }
        }
        return $datas;
    }

    public function differenceSettlementResponseProcess($brand, $date)
    {
        $pg = $this->getPGClass($brand);
        if($pg)
        {
            $groups  = $pg->response($date);
            foreach($groups as $code => $new_trans)
            {
                DB::transaction(function () use($code, $new_trans) {
                    $ids = array_column($new_trans, 'trans_id');
                    if(count($ids))
                    {
                        if($code === '00')
                        {
                            $query  = DifferenceSettlementHistory::whereIn('trans_id', $ids);
                            $exist_trans = (clone $query)->get();
                            if(count($exist_trans))
                            {   // new trans와 exist trans의 크기는 항상 같아야함
                                $datas  = $this->getResponseSuccessFormat($exist_trans, $new_trans);
                                if(count($exist_trans) === count($datas))
                                {
                                    (clone $query)->delete();
                                    logging(['delete' => count($exist_trans), 'new' => count($new_trans), 'add' => count($datas)], "difference-settlement-response-add (O)");
                                    $this->manyInsert(new DifferenceSettlementHistory, $datas);    
                                }
                                else
                                    error(['delete' => count($exist_trans), 'new' => count($new_trans), 'add' => count($datas)], "difference-settlement-response-add (X)");
                            }
                        }
                        else
                        {
                            DifferenceSettlementHistory::whereIn('trans_id', $ids)
                                ->update([
                                    'settle_result_code' => $new_trans[0]['settle_result_code'],
                                    'settle_result_msg' => $new_trans[0]['settle_result_msg'],
                                    'mcht_section_code' => $new_trans[0]['mcht_section_code'],
                                ]);
                        }
                    }
                });
            }
        }
    }

    public function getNotApplyTransactionIds($brand, $yesterday, $start_day, $end_day)
    {
        // 성공건 조회
        $success_trans_query  = Transaction::join('payment_gateways', 'transactions.pg_id', '=', 'payment_gateways.id')
            ->join('difference_settlement_histories', 'transactions.id', '=', 'difference_settlement_histories.trans_id')
            ->where('payment_gateways.pg_type', $brand->pg_type)
            ->where(function ($query) use ($yesterday, $start_day, $end_day) {
                $yesterday_s_dt = "{$yesterday} 00:00:00";
                $yesterday_e_dt = "{$yesterday} 23:59:59";
                $s_dt = "{$start_day} 00:00:00";
                $e_dt = "{$end_day} 23:59:59";                
                $query
                    ->whereBetween('transactions.trx_at', [$yesterday_s_dt, $yesterday_e_dt])
                    ->orWhereBetween('transactions.trx_at', [$s_dt, $e_dt]);
            })
            ->where(function ($query) {
                $query
                    ->where('difference_settlement_histories.settle_result_code', '00')
                    ->orWhere('difference_settlement_histories.settle_result_code', '0000');
            })
            ->select('transactions.id');

        // 미반영 거래건 조회
        return Transaction::join('payment_gateways', 'transactions.pg_id', '=', 'payment_gateways.id')
            ->where('payment_gateways.pg_type', $brand->pg_type)
            ->where(function ($query) use ($yesterday, $start_day, $end_day) {
                $yesterday_s_dt = "{$yesterday} 00:00:00";
                $yesterday_e_dt = "{$yesterday} 23:59:59";
                $s_dt = "{$start_day} 00:00:00";
                $e_dt = "{$end_day} 23:59:59";                
                $query
                    ->whereBetween('transactions.trx_at', [$yesterday_s_dt, $yesterday_e_dt])
                    ->orWhereBetween('transactions.trx_at', [$s_dt, $e_dt]);
            })
            ->whereNotIn('transactions.id', $success_trans_query)
            ->pluck('transactions.id')
            ->all();
    }
}
