<?php
namespace App\Http\Controllers\Manager\Settle;

use App\Models\Merchandise\PaymentModule;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Carbon\Carbon;
use App\Models\Log\RealtimeSendHistory;

use App\Http\Controllers\Manager\Salesforce\UnderSalesforce;

class Settle
{
    static public function getDefaultCols($target_settle_amount)
    {
        return [
            'mcht_id', 
            'user_name', 'addr', 'nick_name', 'mcht_name', 'withdraw_fee',
            'phone_num', 'sector', 'business_num', 'resident_num', 
            "acct_num", "acct_name", "acct_bank_name", "acct_bank_code",
            DB::raw("SUM(is_cancel = 0) AS appr_count"),
            DB::raw("SUM(IF(is_cancel = 0, amount, 0)) AS appr_amount"),
            DB::raw("SUM(IF(is_cancel = 0, mcht_settle_fee, 0)) AS appr_settle_fee_amount"),
            DB::raw("SUM(IF(is_cancel = 0, $target_settle_amount, 0)) AS appr_profit"),
            DB::raw("SUM(is_cancel = 1) AS cxl_count"),
            DB::raw("SUM(IF(is_cancel = 1, amount, 0)) AS cxl_amount"),
            DB::raw("SUM(IF(is_cancel = 1, mcht_settle_fee, 0)) AS cxl_settle_fee_amount"),
            DB::raw("SUM(IF(is_cancel = 1, $target_settle_amount, 0)) AS cxl_profit"),
        ];
    }

    static public function getSettleInformation($settles)
    {
        foreach($settles as $settle) 
        {
            $chart = [
                'appr' => [
                    'amount' => (int)$settle->appr_amount,
                    'count' => (int)$settle->appr_count,
                    'profit' => (int)$settle->appr_profit,
                    'hold_amount' => 0,
                    'settle_fee' => (int)$settle->appr_settle_fee_amount,
                ],
                'cxl' => [
                    'amount' => (int)$settle->cxl_amount,
                    'count' => (int)$settle->cxl_count,
                    'profit' => (int)$settle->cxl_profit,
                    'hold_amount' => 0,
                    'settle_fee' => (int)$settle->cxl_settle_fee_amount,
                ],
            ];

            $chart['appr']['total_trx_amount'] = $chart['appr']['amount'] - $chart['appr']['profit'];
            $chart['appr']['trx_amount'] = $chart['appr']['total_trx_amount'] - $chart['appr']['settle_fee'];
            $chart['cxl']['total_trx_amount'] = $chart['cxl']['amount'] - $chart['cxl']['profit'];
            $chart['cxl']['trx_amount'] = $chart['cxl']['total_trx_amount'] - $chart['cxl']['settle_fee'];
            $chart['total'] = [
                'amount' => $chart['appr']['amount'] + $chart['cxl']['amount'],
                'count' => $chart['appr']['count'] + $chart['cxl']['count'],
                'profit' => $chart['appr']['profit'] + $chart['cxl']['profit'],
                'trx_amount' => $chart['appr']['trx_amount'] + $chart['cxl']['trx_amount'],
                'hold_amount' => $chart['appr']['hold_amount'] + $chart['cxl']['hold_amount'],
                'settle_fee' => $chart['appr']['settle_fee'] + $chart['cxl']['settle_fee'],
                'total_trx_amount' => $chart['appr']['amount'] + $chart['cxl']['total_trx_amount']    
            ];
            $settle->total = $chart['total'];
            $settle->appr = $chart['appr'];
            $settle->cxl = $chart['cxl'];

            $settle->deduction = [
                'input' => null,
                'amount' => 0,  // deduct
            ];
            $settle->terminal = [  // terminal
                'amount' => 0,
                'under_sales_amount' => 0,
                'terminal_settle_count' => 0,
            ];
            $settle->settle_transaction_idxs = [];
            $settle->makeHidden([
                'appr_count', 'appr_amount', 'appr_settle_fee_amount', 'appr_profit',
                'cxl_count', 'cxl_amount', 'cxl_settle_fee_amount', 'cxl_profit',
            ]);
        }
        return $settles;
    }

    static public function getTermianlCost($request, $mcht_ids=[])
    {
        $query = PaymentModule::terminalSettle($request->level);
        if(count($mcht_ids))
            $query = PaymentModule::whereIn('mcht_id', $mcht_ids);
        //union all?
        $query->get([
            'payment_modules.begin_dt',
            'payment_modules.comm_settle_type',
            'payment_modules.comm_settle_day',
            'payment_modules.last_settle_month',
            'payment_modules.comm_settle_fee',
            'payment_modules.under_sales_type',
            'payment_modules.id',
            "payment_modules.mcht_id",
        ]);
    }

    static public function getMerchandiseNoSettle($request)
    {
        [$target_id, $target_settle_id, $target_settle_amount] = getTargetInfo($request->level);

        $cols = self::getDefaultCols($target_settle_amount);
        $cols = UnderSalesforce::getViewableSalesCols($request, $cols);

        $query = Transaction::noSettlement($target_settle_id)
            ->join('merchandises', 'transactions.mcht_id', '=', 'merchandises.id')
            ->where('merchandises.use_collect_withdraw', false)
            ->where('mcht_name', 'like', "%".$request->search."%");
        //실시간 포함여부
        if((int)$request->use_realtime_deposit === 1)
        {
            $fails = RealtimeSendHistory::onlyFailRealtime();
            if(count($fails))
                $query = $query->whereNotIn('id', $fails);
        }
        else
            $query = $query->where('transactions.mcht_settle_type', '!=', -1);
        // 모아서 출금
        if($request->mcht_id)
            $query = $query->where('id', $request->mcht_id);

        $settles = $query
            ->groupBy($target_id)
            ->orderBy('mcht_id', 'desc')
            ->with(['deducts', 'settlePayModules'])
            ->get($cols);

        return self::getSettleInformation($settles);
    }


    static public function getNoSettlement()
    {

    }
}
