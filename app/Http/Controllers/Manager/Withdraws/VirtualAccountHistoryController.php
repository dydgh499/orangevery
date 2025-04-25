<?php

namespace App\Http\Controllers\Manager\Withdraws;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Ablilty\Ablilty;
use App\Http\Controllers\Ablilty\AbnormalConnection;

use App\Http\Requests\Manager\Settle\CollectWithdrawRequest;
use App\Http\Requests\Manager\Settle\CollectWithdrawRequestV2;
use App\Http\Controllers\Manager\Withdraws\VirtualAccountController;
use App\Http\Controllers\Manager\Salesforce\UnderSalesforce;
use App\Http\Controllers\Utils\Comm;

use App\Models\Salesforce;
use App\Models\Merchandise;
use App\Models\Transaction;
use App\Models\Withdraws\VirtualAccount;
use App\Models\Withdraws\VirtualAccountHistory;
use App\Models\Log\SettleHistoryMerchandise;

use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;

use App\Http\Requests\Manager\IndexRequest;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VirtualAccountHistoryController extends Controller
{
    use ManagerTrait, ExtendResponseTrait;
    protected $virtual_account_histories;

    public function __construct(VirtualAccountHistory $virtual_account_histories)
    {
        $this->virtual_account_histories = $virtual_account_histories;
    }

    public function dateFilter($request, $query)
    {
        if($request->has('s_dt'))
        {
            $s_dt = strlen($request->s_dt) === 10 ? date($request->s_dt." 00:00:00") : $request->s_dt;
            $query = $query->where('virtual_account_histories.created_at', '>=', $s_dt);
        }
        if($request->has('e_dt'))
        {
            $e_dt = strlen($request->e_dt) === 10 ? date($request->e_dt." 23:59:59") : $request->e_dt;
            $query = $query->where('virtual_account_histories.created_at', '<=', $e_dt);
        } 
        return $query;
    }

    public function common($request)
    {
        $search = $request->input('search', '');
        $level  = (int)$request->input('level', 10);
        $query  = $this->virtual_account_histories
            ->join('virtual_accounts', 'virtual_account_histories.va_id', '=' ,'virtual_accounts.id');
        $query  = VirtualAccountController::getCommonQuery($query, $request);
        if(Ablilty::isMerchandise($request) || $level === 10)
        {
            $query  = $query->where(function ($query) use ($search) {
                    return $query->where('virtual_accounts.account_name', 'like', "%$search%")
                        ->orWhere('virtual_account_histories.trx_id', 'like', "%$search%")
                        ->orWhere('merchandises.mcht_name', 'like', "%$search%");
                });
        }
        else
        {
            $query  = $query->where(function ($query) use ($search) {
                    return $query->where('virtual_accounts.account_name', 'like', "%$search%")
                        ->orWhere('virtual_account_histories.trx_id', 'like', "%$search%")
                        ->orWhere('salesforces.sales_name', 'like', "%$search%");
                });
        }

        if($request->trans_type !== null)
            $query = $query->where('virtual_account_histories.trans_type', $request->trans_type);
        if($request->withdraw_status !== null)
        {
            $query = $query
                ->where('virtual_account_histories.withdraw_status', $request->withdraw_status)
                ->where('virtual_account_histories.trans_type', 1);
        }
        if($request->deposit_type !== null)
        {
            $query = $query
                ->where('virtual_account_histories.deposit_type', $request->deposit_type)
                ->where('virtual_account_histories.trans_type', 0);
        }        
        return $query;
    }
    /**
     * 차트 데이터 출력
     *
     * 가맹점 이상 가능
     */
    public function chart(Request $request)
    {
        $cols = [
            DB::raw("SUM(IF(trans_type = 0 AND deposit_status = 1, trans_amount, 0)) AS deposit_amount"),
            DB::raw("SUM(IF(trans_type = 1 AND (withdraw_status = 0), trans_amount, 0)) AS withdraw_wait_amount"),
            DB::raw("SUM(IF(trans_type = 1 AND (withdraw_status = 1), trans_amount, 0)) AS withdraw_success_amount"),
            DB::raw("SUM(IF(trans_type = 1 AND (withdraw_status = 2), trans_amount, 0)) AS withdraw_error_amount"),
            DB::raw("SUM(IF(trans_type = 1 AND (withdraw_status = 3), trans_amount, 0)) AS withdraw_appr_cancel_amount"),
            DB::raw("SUM(IF(trans_type = 1 AND (withdraw_status = 4), trans_amount, 0)) AS withdraw_book_cancel_amount"),

            DB::raw("SUM(trans_type = 0) AS deposit_count"),
            DB::raw("SUM(IF(trans_type = 1 AND (withdraw_status = 0), 1, 0)) AS withdraw_wait_count"),
            DB::raw("SUM(IF(trans_type = 1 AND (withdraw_status = 1), 1, 0)) AS withdraw_success_count"),
            DB::raw("SUM(IF(trans_type = 1 AND (withdraw_status = 2), 1, 0)) AS withdraw_error_count"),
            DB::raw("SUM(IF(trans_type = 1 AND (withdraw_status = 3), 1, 0)) AS withdraw_appr_cancel_count"),
            DB::raw("SUM(IF(trans_type = 1 AND (withdraw_status = 4), 1, 0)) AS withdraw_book_cancel_count"),

            DB::raw("SUM(IF(trans_type = 1 AND (withdraw_status = 1), virtual_account_histories.withdraw_fee, 0)) AS withdraw_fee_amount"),
        ];
        $query = $this->common($request);
        $query = $this->dateFilter($request, $query);
        $chart = $query->first($cols);
        return $this->response(0, $chart);
    }

    /**
     * 목록출력
     *
     * 운영자 이상 가능
     *
     * @queryParam search string 검색어(아아디)
     */
    public function index(IndexRequest $request)
    {
        $page       = $request->input('page');
        $page_size  = $request->input('page_size');
        $res        = ['page'=>$page, 'page_size'=>$page_size];

        $sp = ($page - 1) * $page_size;
        $cols   = [
            'virtual_account_histories.*',
            'virtual_accounts.account_name',
            'virtual_accounts.account_code',
            VirtualAccountController::getUserNameCol($request),
        ];
        $query = $this->common($request)->with(['withdraws']);
        $query = $this->dateFilter($request, $query);

        $min    = $query->min('virtual_account_histories.id');
        if($min != NULL)
        {
            $con_query      = $query->where('virtual_account_histories.id', '>=', $min);
            $res['total']   = $query->count();

            $con_query = $con_query
                ->orderBy('virtual_account_histories.id', 'desc')
                ->offset($sp)
                ->limit($page_size);
            $res['content'] = count($cols) ? $con_query->get($cols) : $con_query->get();
        }
        else
        {
            $res['total'] = 0;
            $res['content'] = [];
        }
        return $this->response(0, $res);
    }

    public function cancelJob(Request $request)
    {
        if(Ablilty::isOperator($request))
        {
            $validated = $request->validate(['trx_ids.*'=>'required']);
            $url = env('NOTI_URL', 'http://localhost:81').'/api/v2/realtimes/cancel-job';
            $res = Comm::post($url, ['trx_ids' => $request->trx_ids]);
            return $this->apiResponse($res['body']['result_cd'], $res['body']['result_msg']);
        }
        else
            return $this->response(951);
    }

    public function retryWithdraw(Request $request)
    {
        if(Ablilty::isOperator($request))
        {
            $validated = $request->validate([
                'id'=>'required|integer'
            ]);
            $url = env('NOTI_URL', 'http://localhost:81').'/api/v2/realtimes/retry-withdraw';
            $res = Comm::post($url, ['id' => $request->id]);
            return $this->apiResponse($res['body']['result_cd'], $res['body']['result_msg']);
        }
        else
            return $this->response(951);
    }

    public function retrySettlement(Request $request)
    {
        if(Ablilty::isOperator($request))
        {
            $validated = $request->validate([
                'id'        => 'required|integer', 
                'va_id'     => 'required|integer', 
                'user_id'   => 'required|integer', 
                'level'     => 'required|integer'
            ]);
            $url = env('NOTI_URL', 'http://localhost:81').'/api/v2/realtimes/retry-settlement';
            $res = Comm::post($url, [
                'id'        => $request->id,
                'va_id'     => $request->va_id,
                'user_id'   => $request->user_id,
                'level'     => $request->level,
            ]);
            return $this->apiResponse($res['body']['result_cd'], $res['body']['result_msg']);
        }
        else
            return $this->response(951);
    }
    

    /**
     * 출금가능금액 조회
     *
     * 출금가능한금액을 조회합니다.
     */
    public function withdrawsBalance(Request $request)
    {
        $validated = $request->validate(['va_id' => 'required|integer']);
        $url = env('NOTI_URL', 'http://localhost:81').'/api/v2/realtimes/balance';
        $res = Comm::post($url, ['va_id' => $request->va_id]);
        if($res['body']['result_cd'] === '0000') 
        {
            return $this->response(0, [
                'profit'        =>  $res['body']['temp']['withdraw_able_amount'],
                'withdraw_fee'  =>  $res['body']['temp']['withdraw_fee'],
            ]);
        }
        else
            return $this->apiResponse($res['body']['result_cd'], $res['body']['result_msg']);        
    }

    /**
     * 모아서 출금요청
     */
    public function collectWithdraw(Request $request)
    {
        $validated = $request->validate([
            'va_id' => 'required|integer', 
            'withdraw_amount' => 'required|integer',
        ]);
        //전액출금시
        if(Ablilty::isOperator($request))
            $fee_apply = $request->input('fee_apply', 1);
        else
            $fee_apply = 1;

        $url = env('NOTI_URL', 'http://localhost:81').'/api/v2/realtimes/collect';
        $res = Comm::post($url, [
            'va_id'             => $request->va_id,
            'withdraw_amount'   => $request->withdraw_amount,
            'fee_apply'         => $fee_apply,
        ]);
        return $this->apiResponse($res['body']['result_cd'], $res['body']['result_msg']); 
    }

    /**
     * 모아서 출금요청(주누)
     */
    public function collectWithdrawCustom(CollectWithdrawRequestV2 $request)
    {
        $virtual_account = VirtualAccount::where('user_id', $request->user()->id)
            ->where('account_code', $request->samw_code)
            ->where('level', 10)
            ->first();
        if($virtual_account)
        {
            $url = env('NOTI_URL', 'http://localhost:81').'/api/v2/realtimes/collect-custom';
            $res = Comm::post($url, [
                'va_id'             => $virtual_account->id,
                'withdraw_amount'   => $request->withdraw_amount,
                'acct_num'          => $request->acct_num,
                'acct_name'         => $request->acct_name,
                'acct_bank_code'    => $request->acct_bank_code,
                'acct_bank_name'    => $request->acct_bank_name,
                'fee_apply'         => 1,
            ]);
            return $this->apiResponse($res['body']['result_cd'], $res['body']['result_msg']);     
        }
        else
            return $this->extendResponse(1000, "정산지갑을 찾을 수 없습니다.");
    }


    /*
    * 이체내역서
    */
    public function withdrawStatement(Request $request)
    {
        $validated = $request->validate(['id' => 'required|integer']);
        $statement = $this->virtual_account_histories
                ->join('virtual_accounts', "virtual_accounts.id", '=', 'virtual_account_histories.va_id')
                ->join('virtual_account_withdraws', "virtual_account_withdraws.va_history_id", '=', 'virtual_account_histories.id')
                ->join('finance_vans', "virtual_accounts.fin_id", '=', 'finance_vans.id')
                ->where("virtual_account_histories.brand_id", $request->user()->brand_id)
                ->where('virtual_account_withdraws.id', $request->id)
                ->where('virtual_account_withdraws.result_code', '0000')
                ->first([
                    'virtual_accounts.user_id',
                    'virtual_accounts.level',
                    'virtual_account_withdraws.acct_num',
                    'virtual_account_withdraws.acct_bank_name',
                    'virtual_account_withdraws.trans_seq_num',
                    'virtual_account_withdraws.trans_amount',
                    DB::raw("finance_vans.bank_code as withdraw_bank_code"),
                    DB::raw("finance_vans.corp_name as withdraw_corp_name"),
                    "finance_vans.withdraw_acct_num",
                    'virtual_account_withdraws.created_at',
                ]);
        if($statement)
        {
            $query = $statement->level === 10 ? new Merchandise : new Salesforce;
            $user = $query->where('id', $statement->user_id)->first();
            $statement->user_name = $statement->level === 10 ? $user->mcht_name : $user->sales_name;
            $statement->withdraw_acct_num = AbnormalConnection::masking($statement->withdraw_acct_num);
            return $this->response(0, $statement);
        }
        else
            return $this->extendResponse(2000, '이체정보가 존재하지 않습니다.');
    }

    /*
    * 거래대사
    */
    public function tradeAmbassador(Request $request)
    {
        $validated = $request->validate([
            'id'        => 'required|integer',
            'level'     =>  'required|integer',
            'trans_type'=> 'required|integer',
            'settle_id' => 'nullable|integer',
            'deposit_type'  => 'nullable|integer',
            'trx_id'        => 'nullable|string',
        ]);
        $getTradeAmbassador = function ($request, $deposit_type, $trx_id, $settle_id) {
            if((int)$deposit_type < 2 && $trx_id)
            {
                $trans = Transaction::join('payment_modules', 'transactions.pmod_id', '=', 'payment_modules.id')
                    ->join('merchandises', 'transactions.mcht_id', '=', 'merchandises.id')
                    ->where('transactions.trx_id', $trx_id)
                    ->where('transactions.is_cancel', $deposit_type)
                    ->first([
                        'merchandises.mcht_name',
                        'payment_modules.note',
                        'transactions.*',
                    ]);
                $trans = json_decode(json_encode($trans), true);
                $trans = UnderSalesforce::setViewableSalesInfos($request, $trans);
            }
            else
                $trans = [];
            $settle = SettleHistoryMerchandise::join('merchandises', 'settle_histories_merchandises.mcht_id', '=', 'merchandises.id')
                ->where('settle_histories_merchandises.id', $settle_id)
                ->first([
                    'merchandises.mcht_name',
                    'settle_histories_merchandises.*',
                ]);

            return [$trans, $settle];
        };

        if((int)$request->trans_type === 0)
        {
            [$trans, $settle] = $getTradeAmbassador($request, $request->deposit_type, $request->trx_id, $request->settle_id);
            return $this->response(0, [
                'trans'     => $trans,
                'settle'    => $settle,
            ]);
        }
        else
        {
            $history = $this->virtual_account_histories
                ->where('trans_type', 0)
                ->where('brand_id', $request->user()->brand_id)
                ->where('cxl_seq', 0)
                ->where('level', $request->level)
                ->where('trx_id', $request->trx_id)
                ->first();
            if($history)
                [$trans, $settle] = $getTradeAmbassador($request, $history->deposit_type, $history->trx_id, $history->settle_id);
            else
            {
                $trans = [];
                $settle = [];
                $history = [];
            }
            return $this->response(0, [
                'trans'     => $trans,
                'settle'    => $settle,
                'history'   => $history,
            ]);
        }

    }
}
