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
        $query  = $this->payModules
            ->where('brand_id', $request->user()->brand_id)
            ->where('module_type', 0);

        if($request->has('mcht_id'))
            $query = $query->where('mcht_id', $request->mcht_id);

        $query = $query->where(function ($query) use ($search) {
            return $query->where('mid', 'like', "%$search%")
                ->orWhere('tid', 'like', "%$search%");
        });



        $data = $this->getIndexData($request, $query);
        return $this->response(0, $data);
    }
}
