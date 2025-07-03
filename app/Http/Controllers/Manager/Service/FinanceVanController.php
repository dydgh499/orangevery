<?php

namespace App\Http\Controllers\Manager\Service;

use App\Models\Service\FinanceVan;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Traits\StoresTrait;

use App\Http\Requests\Manager\IndexRequest;
use App\Http\Requests\Manager\Service\FinanceRequest;

use App\Http\Controllers\Ablilty\Ablilty;
use App\Http\Controllers\Ablilty\EditAbleWorkTime;
use App\Http\Controllers\Ablilty\ActivityHistoryInterface;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * @group FinanceVan API
 *
 * 금융벤 API입니다.
 */
class FinanceVanController extends Controller
{
    use ManagerTrait, ExtendResponseTrait, StoresTrait;
    protected $finance_vans;
    protected $target;

    public function __construct(FinanceVan $finance_vans)
    {
        $this->finance_vans = $finance_vans;
        $this->target = '금융 VAN';
    }
    
    /**
     * 목록출력
     *
     * 본사 이상 가능
     */
    public function index(IndexRequest $request)
    {
        $query = $this->finance_vans->where('is_delete', false);
        $query = brandFilter($query, $request);
        return $this->response(0, $query->get());
    }

    /**
     * 추가
     *
     * 본사 이상 가능
     *
     */
    public function store(FinanceRequest $request)
    {
        $data = $request->data();

        if(EditAbleWorkTime::validate() === false)
            return $this->extendResponse(1500, '지금은 작업할 수 없습니다.');
        else
        {
            $res = app(ActivityHistoryInterface::class)->add($this->target, $this->finance_vans, $data, 'nick_name');
            if($res)
                return $this->response(1, ['id' => $res->id]);    
            else
                return $this->response(990, []);
        }
    }

    /**
     * 단일조회
     *
     * 본사 이상 가능
     *
     * @urlParam id integer required 금융 VAN PK
     */
    public function show(Request $request, int $id)
    {
        $data = $this->finance_vans->where('id', $id)->first();
        if($data)
        {
            if(Ablilty::isBrandCheck($request, $data->brand_id) === false)
                return $this->response(951);
            else
                return $this->response(0, $data);
        }
    }

    /**
     * 업데이트
     *
     * 본사 이상 가능
     *
     * @urlParam id integer required 금융 VAN PK
     */
    public function update(FinanceRequest $request, int $id)
    {
        $data    = $request->data();
        $query   = $this->finance_vans->where('id', $id);
        $finance = $query->first();
        if(Ablilty::isBrandCheck($request, $finance->brand_id) === false)
            return $this->response(951);
        if(EditAbleWorkTime::validate() === false)
            return $this->extendResponse(1500, '지금은 작업할 수 없습니다.');

        $row = app(ActivityHistoryInterface::class)->update($this->target, $query, $data, 'nick_name');
        if($row)
            return $this->response(1, ['id' => $id]);
        else
            return $this->response(990);
    }

    /**
     * 단일삭제
     *
     * @urlParam id integer required 금융VAN PK
     */
    public function destroy(Request $request, int $id)
    {
        $query  = $this->finance_vans->where('id', $id);
        $data   = $query->first();
        if(Ablilty::isBrandCheck($request, $data->brand_id) === false)
            return $this->response(951);
        if(EditAbleWorkTime::validate() === false)
            return $this->extendResponse(1500, '지금은 작업할 수 없습니다.');

        $row    = app(ActivityHistoryInterface::class)->destory($this->target, $query, 'nick_name');
        return $this->response(1, ['id' => $id]);    
    }
}
