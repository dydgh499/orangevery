<?php

namespace App\Http\Controllers\QuickView;

use App\Models\Transaction;

use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class QuickViewController extends Controller
{
    use ManagerTrait, ExtendResponseTrait;

    function index(Request $request)
    {
        $request->merge([
            'page' => '1',
            'page_size'=> '9999999',
        ]);
        $query = Transaction::where('is_delete', false)
            ->where('brand_id', $request->user()->brand_id);

        $query = globalAuthFilter($query, $request);        
        $data = $this->getIndexData($request, $query);
        return $this->response(0, $data);
    }
}
