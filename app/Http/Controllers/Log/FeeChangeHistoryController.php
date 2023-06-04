<?php

namespace App\Http\Controllers\Log;

use App\Models\Logs\MChtFeeChangeHistory;
use App\Models\Logs\SfFeeChangeHistory;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Requests\Manager\IndexRequest;


class FeeChangeHistoryController extends Controller
{
    use ManagerTrait, ExtendResponseTrait;
    protected $mcht_fee_histories;
    protected $sf_fee_histories;

    public function __construct(MChtFeeChangeHistory $mcht_fee_histories, SfFeeChangeHistory $sf_fee_histories)
    {
        $this->mcht_fee_histories   = $mcht_fee_histories;
        $this->sf_fee_histories     = $sf_fee_histories;
    }

    public function merchandise(IndexRequest $request)
    {
        $search = $request->input('search', '');
        $query  = $this->mcht_fee_histories
            ->join('merchandises', 'mcht_fee_change_histories.mcht_id', '=', 'merchandises.id')
            ->where('mcht_fee_change_histories.brand_id', $request->user()->brand_id)
            ->where('merchandises.mcht_name', 'like', "%$search%");

        $data = $this->getIndexData($request, $query, 'mcht_fee_change_histories.id', ['mcht_fee_change_histories.*', 'merchandises.mcht_name'], 'mcht_fee_change_histories.created_at');
        return $this->response(0, $data);
    }

    public function salesforce(IndexRequest $request)
    {
        $search = $request->input('search', '');
        $query  = $this->sf_fee_histories
            ->join('merchandises', 'sf_fee_change_histories.mcht_id', '=', 'merchandises.id')
            ->where('sf_fee_change_histories.brand_id', $request->user()->brand_id)
            ->where('merchandises.mcht_name', 'like', "%$search%");

        $query = $query->with(['bfSales', 'aftSales']);
        $data = $this->getIndexData($request, $query, 'sf_fee_change_histories.id', ['sf_fee_change_histories.*', 'merchandises.mcht_name'], 'sf_fee_change_histories.created_at');
        return $this->response(0, $data);
    }
}
