<?php

namespace App\Http\Controllers\Manager\Service;

use App\Models\Service\FinanceVan;
use App\Models\Service\HeadOfficeAccount;
use App\Models\Service\CMSTransaction;
use App\Models\Log\SettleHistoryMerchandiseDeposit;
use App\Models\Log\SettleHistorySalesforceDeposit;

use App\Http\Traits\StoresTrait;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Auth\AuthPhoneNum;
use App\Http\Controllers\Ablilty\Ablilty;
use App\Http\Controllers\Utils\Comm;
use App\Http\Requests\Manager\IndexRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * @group CMS Transaciton API
 *
 * CMS 거래 API 입니다.
 */
class CMSTransactionController extends Controller
{
    use ManagerTrait, ExtendResponseTrait, StoresTrait;
    protected $cms_transactions;

    public function __construct(CMSTransaction $cms_transactions)
    {
        $this->cms_transactions = $cms_transactions;
    }

    private function commonSelect($request)
    {
        $search = $request->input('search', '');
        $query = $this->cms_transactions->where('brand_id', $request->user()->brand_id)
            ->where('trx_at', '>=', $request->s_dt.' 00:00:00')
            ->where('trx_at', '<=', $request->e_dt.' 23:59:59')
            ->where(function ($query) use ($search) {
                return $query->where('acct_num', 'like', "%$search%")
                    ->orWhere('note', 'like', "%$search%");
            });
        if($request->fin_id !== null)
            $query = $query->where('fin_id', $request->fin_id);
        if($request->is_withdraw !== null)
            $query = $query->where('is_withdraw', $request->is_withdraw);

        return $query;
    }

    public function chart(Request $request)
    {
        $getOtherWithdraw = function($orm, $request, $fin_id = null) {
            $search = $request->input('search', '');
            $query = $orm->where('brand_id', $request->user()->brand_id)
                ->where('created_at', '>=', $request->s_dt.' 00:00:00')
                ->where('created_at', '<=', $request->e_dt.' 23:59:59')
                ->where('result_code', '0000')
                ->where(function ($query) use ($search) {
                    return $query->where('acct_num', 'like', "%$search%");
                });
            if($request->fin_id !== null && $fin_id)
                $query = $query->where($fin_id, $request->fin_id);
            return $query;
        };
        $getPaymentAgencyWithdraw = function($orm, $request, $dest_type, $dest_key) {
            $search = $request->input('search', '');
            $history_table = "settle_histories_$dest_type";
            $deposit_table = "settle_histories_$dest_type"."_deposits";

            $query = $orm->join($history_table, $deposit_table.".settle_hist_$dest_key", '=', $history_table.".id")
                ->where($deposit_table.'.brand_id', $request->user()->brand_id)
                ->where($deposit_table.'.created_at', '>=', $request->s_dt.' 00:00:00')
                ->where($deposit_table.'.created_at', '<=', $request->e_dt.' 23:59:59')
                ->where($deposit_table.'.result_code', '0000')
                ->where(function ($query) use ($search, $history_table) {
                    return $query->where($history_table.'.acct_num', 'like', "%$search%");
                });
            return $query;
        };

        $data = $this->commonSelect($request)
            ->where('result_code', '0000')
            ->first([
                DB::raw("SUM(IF(is_withdraw = 0, amount, 0)) AS deposit_amount"),
                DB::raw("SUM(IF(is_withdraw = 1, amount, 0)) AS withdraw_amount"),
                DB::raw("SUM(is_withdraw = 0) AS total_deposit_count"),
                DB::raw("SUM(is_withdraw = 1) AS total_withdraw_count"),
            ]);

        $mcht_payment_agency = $getPaymentAgencyWithdraw(new SettleHistoryMerchandiseDeposit, $request, 'merchandises', 'mcht_id')
            ->first([DB::raw('SUM(deposit_amount) as total_amount')]);
        $sales_payment_agency = $getPaymentAgencyWithdraw(new SettleHistorySalesforceDeposit, $request, 'salesforces', 'sales_id')
            ->first([DB::raw('SUM(deposit_amount) as total_amount')]);

        $data->total_realtime_withdraw_amount = 0;
        $data->total_collect_withdraw_amount  = 0;
        $data->total_payment_agency_withdraw_amount = ((int)$mcht_payment_agency->total_amount + (int)$sales_payment_agency->total_amount)  * -1;
        return $this->response(0, $data);
    }

    public function index(Request $request)
    {
        $query = $this->commonSelect($request);
        $data = $this->getIndexData($request, $query, 'trx_at', [], 'trx_at', false);
        return $this->response(0, $data);
    }

    /*
    * 예금주 조회
    */
    public function ownerCheck(Request $request)
    {
        $data = $request->all();
        $res = Comm::post(env('NOTI_URL', 'http://localhost:81').'/api/v2/realtimes/owner-check', $data);
        if($res['body']['result'] === 100)
            return $this->response(1, ['message'=> $res['body']['message']]);
        else
            return $this->extendResponse(1999, $res['body']['message'], $res['body']['data']);
    }

    /**
     * 잔액조회
     */
    public function getBalance(Request $request)
    {
        $data = $request->all();
        $res = Comm::post(env('NOTI_URL', 'http://localhost:81').'/api/v2/realtimes/get-balance', $data);
        if(isset($res['body']['data']))
        {
            $res = $res['body']['data'];
            return $this->extendResponse($res['result_cd'] === "0000" ? 1 : 2000, $res['result_msg'], $res['data']);    
        }
        else
        {
            if(isset($res['body']['code']) && isset($res['body']['message']))
                return $this->apiResponse($res['body']['code'], $res['body']['message'], []);
            else
                return $this->apiResponse($res['code'], $res['body'], []);
        }
    }

    /**
     * 본사지정계좌 출금
     */
    public function withdraw(Request $request)
    {
        $validated = $request->validate([
            'fin_id' => 'required|integer',
            'head_office_acct_id' => 'required|integer',
            'withdraw_amount' => 'required|integer',
            'note' => 'required|string',
            'token' => 'required|string',
        ]);

        if(AuthPhoneNum::validate($request->token) === 0)
        {
            if($request->user()->tokenCan(35))
            {
                $data = $request->all();
                // parameter 변조 방지
                $privacy = HeadOfficeAccount::where('id', $request->head_office_acct_id)
                    ->where('brand_id', $request->user()->brand_id)
                    ->first();
                $finance = FinanceVan::where('id', $request->fin_id)
                    ->where('brand_id', $request->user()->brand_id)
                    ->first();

                $params = [
                    'fin_id' => $request->fin_id,
                    'amount' => $request->withdraw_amount,
                    'note' => $request->note,
                ];

                if($privacy && $finance)
                {
                    $params = array_merge($params, $privacy->toArray());
                    $res    = Comm::post(env('NOTI_URL', 'http://localhost:81').'/api/v2/realtimes/operate-withdraw', $params);
                    return $this->apiResponse($res['body']['result_cd'], $res['body']['result_msg']);    
                }
                else
                    return $this->response(951);
            }
            else
                return $this->response(951);
        }
        else
            return $this->response(951);
    }
}
