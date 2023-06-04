<?php

namespace App\Http\Controllers\Log;

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
        $search = $request->input('search', '');
        $query  = $this->posts
            ->where('brand_id', $request->user()->brand_id)
            ->where('title', 'like', "%$search%");

        $data = $this->getIndexData($request, $query);
        return $this->response(0, $data);
    }
}
