<?php

namespace App\Http\Controllers\Manager\Service;

use App\Models\Service\FinanceVan;
use App\Models\Service\CMSTransaction;

use App\Http\Traits\StoresTrait;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Utils\Comm;
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
        $data = $this->commonSelect($request)
            ->where('result_code', '0000')
            ->first([
                DB::raw("SUM(IF(is_withdraw = 0, amount, 0)) AS deposit_amount"),
                DB::raw("SUM(IF(is_withdraw = 1, amount, 0)) AS withdraw_amount"),
                DB::raw("SUM(is_withdraw = 0) AS total_deposit_count"),
                DB::raw("SUM(is_withdraw = 1) AS total_withdraw_count"),
            ]);

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
}
