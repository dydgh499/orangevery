<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Requests\Manager\IndexRequest;

class SettleController extends Controller
{
    use ManagerTrait, ExtendResponseTrait;
    
    public function __construct(Transaction $transactions)
    {
        $this->transactions = $transactions;
    }

    public function merchandises(IndexRequest $request)
    {
        return [];
    }

    public function salesforces(IndexRequest $request)
    {
        return [];
    }
}
