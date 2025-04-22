<?php

namespace App\Http\Controllers\Manager\Withdraws;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Ablilty\Ablilty;
use App\Http\Controllers\Ablilty\AbnormalConnection;
use App\Http\Controllers\Manager\Withdraws\VirtualAccountController;

use App\Models\Withdraws\VirtualAccount;
use App\Models\Withdraws\VirtualAccountWithdraw;

use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;

use App\Http\Requests\Manager\IndexRequest;

use Illuminate\Http\Request;

class VirtualAccountWithdrawController extends Controller
{
    use ManagerTrait, ExtendResponseTrait;
    protected $virtual_account_withdraws;

    public function __construct(VirtualAccountWithdraw $virtual_account_withdraws)
    {
        $this->virtual_account_withdraws = $virtual_account_withdraws;
        $this->target   = '';
    }

    /**
     * 목록출력
     *
     * @queryParam search string 검색어(아아디)
     */
    public function index(IndexRequest $request)
    {
        $search = $request->input('search', '');
        $level  = (int)$request->input('level', 10);
        $cols   = [
            'virtual_account_withdraws.*',
            'virtual_accounts.account_name',
            'virtual_accounts.account_code',
        ];
        $cols[] = VirtualAccountController::getUserNameCol($request);
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
                $query = $query->whereNotIn('virtual_account_withdraws.result_code', ['PV484', '0000']);
        }
        
        $data = $this->getIndexData($request, $query, 'virtual_account_withdraws.id', $cols, 'virtual_account_withdraws.created_at');
        return $this->response(0, $data);
    }
}
