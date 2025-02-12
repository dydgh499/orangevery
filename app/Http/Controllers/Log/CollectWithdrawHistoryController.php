<?php

namespace App\Http\Controllers\Log;

use App\Models\Merchandise;
use App\Models\Merchandise\PaymentModule;
use App\Models\CollectWithdraw;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;

use App\Http\Requests\Manager\Settle\CollectWithdrawRequest;
use App\Http\Requests\Manager\IndexRequest;

use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * @group Collect Withdraw History API
 *
 * 모아서 출금 이력 API 입니다.
 */
class CollectWithdrawHistoryController extends Controller
{
    use ManagerTrait, ExtendResponseTrait;
    protected $collect_withdraws;

    public function __construct(CollectWithdraw $collect_withdraws)
    {
        $this->collect_withdraws = $collect_withdraws;
    }

    public function commonSelect(IndexRequest $request)
    {
        $search = $request->input('search', '');
        $query = $this->collect_withdraws->join('merchandises', 'merchandises.id', '=', 'collect_withdraws.mcht_id')
            ->where('collect_withdraws.brand_id', $request->user()->brand_id)
            ->where('merchandises.mcht_name', 'like', "%$search%");
        $query = globalSalesFilter($query, $request, 'merchandises');
        $query = globalAuthFilter($query, $request, 'merchandises');
        return $query;
    }

    /**
     * 목록출력
     *
     * 운영자 이상 가능
     *
     * @queryParam search string 검색어(제목)
     */
    public function index(IndexRequest $request)
    {
        $cols = [
            'collect_withdraws.*',
            'merchandises.mcht_name',
        ];
        $query = $this->commonSelect($request);
        return $this->getIndexData($request, $query, 'collect_withdraws.id', $cols, 'collect_withdraws.created_at');
    }
}
