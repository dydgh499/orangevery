<?php

namespace App\Http\Controllers\Manager\Service;

use App\Models\BankAccount;
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
 * 은행계좌 API 입니다.
 */
class BankAccountController extends Controller
{
    use ManagerTrait, ExtendResponseTrait, StoresTrait;
    protected $bank_accounts;

    public function __construct(BankAccount $bank_accounts)
    {
        $this->bank_accounts = $bank_accounts;
    }

    public function index(IndexRequest $request)
    {
        $search = $request->search;
        $query = $this->bank_accounts
        ->where('brand_id', $request->user()->brand_id)
        ->where(function ($query) use ($search) {
            return $query->where('acct_num', 'like', "%$search%")
                ->orWhere('note', 'like', "%$search%");
        });
        
        $data = $this->getIndexData($request, $query);
        return $this->response(0, $data);
    }

}
