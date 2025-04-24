<?php

namespace App\Http\Controllers\Manager\Withdraws;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Ablilty\Ablilty;
use App\Http\Controllers\Manager\Withdraws\VirtualAccountController;

use App\Models\Withdraws\VirtualAccountWithdraw;

use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;

use App\Http\Requests\Manager\IndexRequest;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VirtualAccountWithdrawController extends Controller
{
    use ManagerTrait, ExtendResponseTrait;
    protected $virtual_account_withdraws;

    public function __construct(VirtualAccountWithdraw $virtual_account_withdraws)
    {
        $this->virtual_account_withdraws = $virtual_account_withdraws;
    }

    public function chart(Request $request)
    {
        $cols = [
            DB::raw("SUM(IF(result_code = '0000', virtual_account_withdraws.trans_amount, 0)) AS withdraw_success_amount"),
            DB::raw("SUM(IF((result_code != 'PV484' AND result_code != '0000' AND result_code != '-5'), virtual_account_withdraws.trans_amount, 0)) AS withdraw_error_amount"),
            DB::raw("SUM(IF(result_code = 'PV484', virtual_account_withdraws.trans_amount, 0)) AS withdraw_appr_cancel_amount"),
            DB::raw("SUM(IF(result_code = '-5', virtual_account_withdraws.trans_amount, 0)) AS withdraw_book_cancel_amount"),

            DB::raw("SUM(IF(result_code = '0000', 1, 0)) AS withdraw_success_count"),
            DB::raw("SUM(IF((result_code != 'PV484' AND result_code != '0000' AND result_code != '-5'), 1, 0)) AS withdraw_error_count"),
            DB::raw("SUM(IF(result_code = 'PV484', 1, 0)) AS withdraw_appr_cancel_count"),
            DB::raw("SUM(IF(result_code = '-5', 1, 0)) AS withdraw_book_cancel_count"),

            DB::raw("SUM(IF(result_code = '0000' = 1, virtual_account_histories.withdraw_fee, 0)) AS withdraw_fee_amount"),
        ];
        $query = $this->common($request);
        if($request->has('s_dt'))
        {
            $s_dt = strlen($request->s_dt) === 10 ? date($request->s_dt." 00:00:00") : $request->s_dt;
            $query = $query->where('virtual_account_withdraws.created_at', '>=', $s_dt);
        }
        if($request->has('e_dt'))
        {
            $e_dt = strlen($request->e_dt) === 10 ? date($request->e_dt." 23:59:59") : $request->e_dt;
            $query = $query->where('virtual_account_withdraws.created_at', '<=', $e_dt);
        }        
        $chart = $query->first($cols);
        return $this->response(0, $chart);
    }

    public function common($request)
    {
        $search = $request->input('search', '');
        $level  = (int)$request->input('level', 10);
        $query  = $this->virtual_account_withdraws
            ->join('virtual_account_histories', 'virtual_account_withdraws.va_history_id', '=' ,'virtual_account_histories.id')
            ->join('virtual_accounts', 'virtual_account_histories.va_id', '=' ,'virtual_accounts.id');
        $query  = VirtualAccountController::getCommonQuery($query, $request);

        if(Ablilty::isMerchandise($request) || $level === 10)
        {
            $query  = $query->where(function ($query) use ($search) {
                return $query->where('virtual_accounts.account_name', 'like', "%$search%")
                    ->orWhere('virtual_account_withdraws.acct_num', 'like', "%$search%")
                    ->orWhere('virtual_account_histories.trx_id', 'like', "%$search%")
                    ->orWhere('merchandises.mcht_name', 'like', "%$search%");
            });
        }
        else
        {
            $query  = $query->where(function ($query) use ($search) {
                return $query->where('virtual_accounts.account_name', 'like', "%$search%")
                    ->orWhere('virtual_account_withdraws.acct_num', 'like', "%$search%")
                    ->orWhere('virtual_account_histories.trx_id', 'like', "%$search%")
                    ->orWhere('salesforces.sales_name', 'like', "%$search%");
            });
        }

        if($request->result_code !== null)
        {
            if($request->result_code === '0000')
                $query = $query->where('virtual_account_withdraws.result_code', '0000');
            else if($request->result_code === 'PV484')
                $query = $query->where('virtual_account_withdraws.result_code', 'PV484');
            else if($request->result_code === '-5')
                $query = $query->where('virtual_account_withdraws.result_code', '-5');
            else
                $query = $query->whereNotIn('virtual_account_withdraws.result_code', ['PV484', '0000', '-5']);
        }
        return $query;
    }
    /**
     * 목록출력
     *
     * @queryParam search string 검색어(아아디)
     */
    public function index(IndexRequest $request)
    {
        $cols   = [
            'virtual_account_withdraws.*',
            'virtual_accounts.account_name',
            'virtual_accounts.account_code',
        ];
        $cols[] = VirtualAccountController::getUserNameCol($request);
        $query = $this->common($request);
        
        $data = $this->getIndexData($request, $query, 'virtual_account_withdraws.id', $cols, 'virtual_account_withdraws.created_at');
        return $this->response(0, $data);
    }
}
