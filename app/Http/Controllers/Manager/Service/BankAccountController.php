<?php

namespace App\Http\Controllers\Manager\Service;

use App\Models\BankAccount;
use App\Http\Traits\StoresTrait;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;

use App\Http\Requests\Manager\IndexRequest;
use App\Http\Controllers\Controller;

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

    /*
    * 목록출력
    */
    public function index(IndexRequest $request)
    {
        $search = $request->search;
        $query = brandFilter($this->bank_accounts, $request);
        $query = $query->where(function ($query) use ($search) {
            return $query->where('acct_num', 'like', "%$search%");
        });
        $data = $this->getIndexData($request, $query);
        return $this->response(0, $data);
    }
    
    /**
     * 단일삭제
     *
     * @urlParam id integer required 계좌 PK
     */
    public function destroy(int $id)
    {
        $res = $this->bank_accounts->where('id', $id)->delete();
        return $this->response($res ? 1 : 990, ['id'=>$id]);
    }

}
