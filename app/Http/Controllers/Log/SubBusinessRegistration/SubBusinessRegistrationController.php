<?php

namespace App\Http\Controllers\Log\SubBusinessRegistration;

use Carbon\Carbon;
use App\Models\Log\SubBusinessRegistration;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

use App\Http\Traits\StoresTrait;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Requests\Manager\IndexRequest;

/**
 * @group Sub-Busniess-Registration API
 *
 * 하위사업자등록 API 입니다.
 */
class SubBusinessRegistrationController extends Controller
{
    use ManagerTrait, ExtendResponseTrait, StoresTrait;
    protected $sub_business_registrations;
    
    public function __construct(SubBusinessRegistration $sub_business_registrations)
    {
        $this->sub_business_registrations = $sub_business_registrations;    
    }

    public function index(IndexRequest $request)
    {
        $query = $this->sub_business_registrations
            ->join('merchandises', 'sub_business_registrations.mcht_id', '=', 'merchandises.id')
            ->where('merchandises.brand_id', $request->user()->brand_id);

        $query = globalPGFilter($query, $request, 'sub_business_registrations');
        $query = globalSalesFilter($query, $request, 'merchandises');
        $query = globalAuthFilter($query, $request, 'merchandises');

        $data = $this->getIndexData($request, $query, 'sub_business_registrations.id', ['sub_business_registrations.*', 'merchandises.mcht_name'], 'sub_business_registrations.created_at');
        return $this->response(0, $data);
    }
}
