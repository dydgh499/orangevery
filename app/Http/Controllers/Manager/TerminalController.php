<?php

namespace App\Http\Controllers\Manager;

use App\Models\PaymentModule;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Requests\Manager\PayModuleRequest;
use App\Http\Requests\Manager\IndexRequest;
use App\Http\Traits\StoresTrait;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TerminalController extends Controller
{
    use ManagerTrait, ExtendResponseTrait, StoresTrait;
    protected $payModules;

    public function __construct(PaymentModule $payModules)
    {
        $this->payModules = $payModules;
        $this->imgs = [];
    }

    /**
     * 목록출력
     *
     * 가맹점 이상 가능
     *
     * @queryParam search string 검색어(mid, tid)
     */
    public function index(IndexRequest $request)
    {
        $module_type = $request->input('module_type', '');
        $search = $request->input('search', '');
        $query = $this->payModules
            ->join('merchandises', 'payment_modules.mcht_id', '=', 'merchandises.id')
            ->where('payment_modules.is_delete', false);

        $query = globalPGFilter($query, $request, 'payment_modules');
        $query = globalSalesFilter($query, $request, 'merchandises');
        $query = globalAuthFilter($query, $request, 'merchandises');

        $query  = $query
                ->where('payment_modules.brand_id', $request->user()->brand_id)
                ->where('payment_modules.module_type', 0);

        $query = $query->where(function ($query) use ($search) {
            return $query->where('payment_modules.mid', 'like', "%$search%")
                ->orWhere('payment_modules.tid', 'like', "%$search%")
                ->orWhere('payment_modules.serial_num', 'like', "%$search%")
                ->orWhere('merchandises.mcht_name', 'like', "%$search%");
        });

        $data = $this->getIndexData($request, $query, 'payment_modules.id', ['payment_modules.*', 'merchandises.mcht_name'], 'payment_modules.created_at');
        return $this->response(0, $data);
    }
}
