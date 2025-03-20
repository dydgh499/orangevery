<?php

namespace App\Http\Controllers\Manager\Salesforce;

use App\Models\Salesforce\SalesforceFeeTable;

use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;

use App\Http\Requests\Manager\IndexRequest;
use App\Http\Requests\Manager\Salesforce\SalesforceFeeTableRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * @group fee table API
 *
 */
class FeeTableController extends Controller
{
    use ManagerTrait, ExtendResponseTrait;
    protected $salesforce_fee_table;

    public function __construct(SalesforceFeeTable $salesforce_fee_table)
    {
        $this->salesforce_fee_table = $salesforce_fee_table;
    }

    public function index(Request $request)
    {
        $query  = $this->salesforce_fee_table->where('brand_id', $request->user()->brand_id);
        $data   = $this->getIndexData($request, $query);
        return $this->response(0, $data);
    }

    public function store(SalesforceFeeTableRequest $request)
    {
        $data   = $request->data();
        $res    = $this->salesforce_fee_table->create($data);
        return $this->response(1, ['id'=>$res->id]);
    }

    public function update(SalesforceFeeTableRequest $request, $id)
    {
        $data   = $request->data();
        $res    = $this->salesforce_fee_table->where('id', $id)->update($data);
        return $this->response(1, ['id'=>$id]);
    }

    public function destroy(Request $request, int $id)
    {
        $res    = $this->salesforce_fee_table->where('id', $id)->delete();
        return $this->response(1, ['id'=>$id]);
    }
}
