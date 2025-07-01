<?php

namespace App\Http\Controllers\Manager\Service;

use App\Models\Service\CMSTransactionBooks;

use App\Http\Traits\StoresTrait;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;

use App\Http\Requests\Manager\IndexRequest;
use App\Http\Controllers\Controller;

/**
 * @group CMS Transaciton Book API
 *
 * CMS 거래 예약 API 입니다.
 */
class CMSTransactionBookController extends Controller
{
    use ManagerTrait, ExtendResponseTrait, StoresTrait;
    protected $cms_transaction_books;

    public function __construct(CMSTransactionBooks $cms_transaction_books)
    {
        $this->cms_transaction_books = $cms_transaction_books;
    }

    /*
    * 목록출력
    */
    public function index(IndexRequest $request)
    {
        $search = $request->search;
        $query = $this->cms_transaction_books
        ->where('brand_id', $request->user()->brand_id)
        ->where(function ($query) use ($search) {
            return $query->where('acct_num', 'like', "%$search%")
                ->orWhere('note', 'like', "%$search%");
        });
        if($request->fin_id !== null)
            $query = $query->where('fin_id', $request->fin_id);
        if($request->is_withdraw !== null)
            $query = $query->where('is_withdraw', $request->is_withdraw);
        
        $data = $this->getIndexData($request, $query);
        return $this->response(0, $data);
    }
}
