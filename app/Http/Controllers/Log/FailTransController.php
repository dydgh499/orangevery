<?php

namespace App\Http\Controllers\Log;

use App\Models\Logs\FailTransaction;
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
        $search = $request->input('search', '');
        $query  = $this->fail_transactions
            ->join('merchandises', 'fail_transactions.mcht_id', '=', 'merchandises.id')
            ->where('fail_transactions.brand_id', $request->user()->brand_id)
            ->where('merchandises.mcht_name', 'like', "%$search%");

        $data = $this->getIndexData($request, $query, 'fail_transactions.id', ['mcht_fee_change_histories.*', 'merchandises.mcht_name'], 'fail_transactions.created_at');
        return $this->response(0, $data);
    }
}
