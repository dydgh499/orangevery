<?php

namespace App\Http\Controllers\Manager;

use App\Models\Transaction;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Requests\Manager\TransactionRequest;
use App\Http\Requests\Manager\IndexRequest;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    use ManagerTrait, ExtendResponseTrait;
    protected $transactions;

    public function __construct(Transaction $transactions)
    {
        $this->transactions = $transactions;
    }

    /**
     * 목록출력
     *
     * 가맹점 이상 가능
     *
     */
    public function index(IndexRequest $request)
    {
        $query =  $this->transactions->where('transactions.brand_id', $request->user()->brand_id);
        $query = globalPGFilter($query, $request, 'transactions');
        $query = globalSalesFilter($query, $request, 'transactions');  
        $query = $query->with(['sales0', 'sales1', 'sales2', 'sales3', 'sales4', 'sales5', 'mcht']);
        $data = $this->getIndexData($request, $query);        
        return $this->response(0, $data);
    }

    /**
     * 추가
     *
     * 대리점 이상 가능
     *
     * @bodyParam user_pw string 유저 패스워드
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(TransactionRequest $request)
    {
        $user = $request->data();
        $res = $this->transactions->create($user);
        return $this->response($res ? 1 : 990);
    }

    /**
     * 단일조회
     *
     * 가맹점 이상 가능
     *
     * @urlParam id integer required 유저 PK
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, $id)
    {
        $data = $this->transactions->where('id', $id)->first();
        return $data ? $this->response(0, $data) : $this->response(1000);
    }

    /**
     * 업데이트
     *
     * 가맹점 이상 가능
     *
     * @urlParam id integer required 유저 PK
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(TransactionRequest $request, $id)
    {
        $data = $request->data();
        $res = $this->transactions->where('id', $id)->update($data);
        return $this->response($res ? 1 : 990);
    }

    /**
     * 단일삭제
     *
     * @urlParam id integer required 유저 PK
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, $id)
    {
        if($this->authCheck($request->user(), $id, 35))
        {
            $res = $this->delete($this->transactions->where('id', $id));
            return $this->response($res);
        }
        else
            return $this->response(951);
    }
}
