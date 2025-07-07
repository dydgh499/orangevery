<?php

namespace App\Http\Controllers\Manager\Service;

use App\Models\Service\CMSTransaction;
use App\Models\Service\CMSTransactionHistory;

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
    protected $cms_transactions, $cms_transaction_histories;

    public function __construct(CMSTransaction $cms_transactions, CMSTransactionHistory $cms_transaction_histories)
    {
        $this->cms_transactions = $cms_transactions;
        $this->cms_transaction_histories = $cms_transaction_histories;
    }

    /**
     * 목록출력 내부기능
     */
    private function common($query, $request)
    {
        $search = $request->input('search', '');
        $query = $query->leftJoin('cms_transaction_histories','cms_transactions.id','=','cms_transaction_histories.ct_id')
            ->where('cms_transactions.brand_id', $request->user()->brand_id)
            ->where('cms_transactions.created_at', '>=', $request->s_dt.' 00:00:00')
            ->where('cms_transactions.created_at', '<=', $request->e_dt.' 23:59:59')
            ->where(function ($query) use ($search) {
                return $query->where('acct_num', 'like', "%$search%");
            });;

        if($request->withdraw_status !== null)
        $query = $query->where('withdraw_status', $request->withdraw_status);

        return $query;
    }
    
    /**
     * 목록출력
     */
    public function index(Request $request)
    {
        $page       = $request->input('page');
        $page_size  = $request->input('page_size');
        $res        = ['page'=>$page, 'page_size'=>$page_size];

        $sp = ($page - 1) * $page_size;
        $cols   = [
            'cms_transactions.*',
        ];
        $query = $this->common($this->cms_transactions, $request)->with(['withdraws']);

        $res['total'] = $query->count();
        $con_query = $query->orderBy('cms_transactions.id', 'desc')
            ->offset($sp)
            ->limit($page_size);
        
        $res['content'] = count($cols) ? $con_query->get($cols) : $con_query->get();
        
        return $this->response(0, $res);
    }

    /**
     * 잔액조회
     */
    public function getBalance(Request $request)
    {
        $data = $request->all();
        $res = Comm::post(env('WITHDRAW_URL', 'http://localhost:81').'/api/v2/realtimes/get-balance', $data);
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
     * 예약 이체 취소
     */
    public function cancelJob(Request $request)
    {
        $validated = $request->validate(['ids.*'=>'required']);
        $url = env('WITHDRAW_URL', 'http://localhost:81').'/api/v2/realtimes/cancel-book-withdraw-job';
        $res = Comm::post($url, ['ids' => $request->ids]);
        return $this->apiResponse($res['body']['result_cd'], $res['body']['result_msg']);
    }
}
