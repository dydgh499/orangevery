<?php

namespace App\Http\Controllers\Log;

use App\Models\Log\FailTransaction;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Requests\Manager\IndexRequest;

class FailTransController extends Controller
{
    use ManagerTrait, ExtendResponseTrait;
    protected $danger_transactions;

    public function __construct(FailTransaction $fail_transactions)
    {
        $this->fail_transactions = $fail_transactions;
    }

    public function index(IndexRequest $request)
    {
        $cols = [
            'fail_transactions.*', 
            'merchandises.mcht_name',
        ];
        $search = $request->input('search', '');
        $query  = $this->fail_transactions
            ->join('payment_modules', 'payment_modules.id', '=', 'fail_transactions.pmod_id')
            ->join('merchandises', 'merchandises.id', '=', 'payment_modules.mcht_id')
            ->where('fail_transactions.brand_id', $request->user()->brand_id)
            ->where('fail_transactions.is_delete', false)
            ->where('merchandises.mcht_name', 'like', "%$search%");

        $query = globalPGFilter($query, $request, 'payment_modules');
        $query = globalSalesFilter($query, $request, 'merchandises');
        $query = globalAuthFilter($query, $request, 'merchandises');

        $data = $this->getIndexData($request, $query, 'fail_transactions.id', $cols, 'fail_transactions.created_at');
        return $this->response(0, $data);
    }
}
