<?php
namespace App\Http\Controllers\Manager\Merchandise;

use App\Models\Transaction;
use App\Models\Merchandise\PaymentModule;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Traits\StoresTrait;
use App\Http\Controllers\Manager\Salesforce\UnderSalesforce;

use App\Http\Requests\Manager\Merchandise\PayModuleRequest;
use App\Http\Requests\Manager\IndexRequest;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;

/**
 * @group Terminal API
 *
 * 장비 API 입니다.
 */
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
        $cols = ['payment_modules.*', 'merchandises.mcht_name'];
        $cols = UnderSalesforce::getViewableSalesCols($request, $cols);

        $search = $request->input('search', '');
        $query = $this->payModules
            ->join('merchandises', 'payment_modules.mcht_id', '=', 'merchandises.id')
            ->where('merchandises.is_delete', false)
            ->where('payment_modules.is_delete', false);

        $query = globalPGFilter($query, $request, 'payment_modules');
        $query = globalSalesFilter($query, $request, 'merchandises');
        $query = globalAuthFilter($query, $request, 'merchandises');
        $query = $query
                ->where('payment_modules.brand_id', $request->user()->brand_id)
                ->where('payment_modules.module_type', 0);

        if($request->ship_out_stat != null)
            $query = $query->where('payment_modules.ship_out_stat', $request->ship_out_stat);
        if($request->un_use)
            $query = $query->notUseLastMonth($request->user()->brand_id);
        
        $query = $query->where(function ($query) use ($search) {
            return $query
                ->where('payment_modules.mid', 'like', "%$search%")
                ->orWhere('payment_modules.tid', 'like', "%$search%")
                ->orWhere('payment_modules.serial_num', 'like', "%$search%")
                ->orWhere('merchandises.mcht_name', 'like', "%$search%");
        });

        $data = $this->getIndexData($request, $query, 'payment_modules.id', $cols, 'payment_modules.created_at');
        return $this->response(0, $data);
    }
}
