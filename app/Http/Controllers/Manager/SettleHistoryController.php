<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Requests\Manager\IndexRequest;


class SettleHistoryController extends Controller
{
    use ManagerTrait, ExtendResponseTrait;
    
    public function index(IndexRequest $request)
    {
        return [];
    }
}
