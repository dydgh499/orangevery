<?php

namespace App\Http\Controllers\Manager;

use App\Models\FinanceVan;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Traits\StoresTrait;

use App\Http\Requests\Manager\IndexRequest;
use App\Http\Requests\Manager\FinanceRequest;

use App\Http\Controllers\Ablilty\Ablilty;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Enums\HistoryType;

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
        $this->imgs = [];
    }
    
    /**
     * 목록출력
     *
     * 본사 이상 가능
     */
    public function index(IndexRequest $request)
    {
        $query = $this->finance_vans
            ->where('brand_id', $request->user()->brand_id)
            ->where('is_delete', false);
        $data = $this->getIndexData($request, $query);
        return $this->response(0, $data);
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

        if(Ablilty::isEditAbleTime() === false)
            return $this->extendResponse(1500, '지금은 작업할 수 없습니다.');
        else
        {
            $res = $this->finance_vans->create($data);        
            operLogging(HistoryType::CREATE, $this->target, [], $data, $data['nick_name']);
            return $this->response($res ? 1 : 990, ['id'=>$res->id]);    
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
        $before = $this->finance_vans->where('id', $id)->first();
        if(Ablilty::isBrandCheck($request, $before->brand_id) === false)
            return $this->response(951);
        if(Ablilty::isEditAbleTime() === false)
            return $this->extendResponse(1500, '지금은 작업할 수 없습니다.');

        $data = $request->data();
        $res = $this->finance_vans->where('id', $id)->update($data);
        operLogging(HistoryType::UPDATE, $this->target, $before, $data, $data['nick_name']);
        return $this->response($res ? 1 : 990, ['id'=>$id]);
    }

    /**
     * 단일삭제
     *
     * @urlParam id integer required 금융VAN PK
     */
    public function destroy(Request $request, int $id)
    {
        $data = $this->finance_vans->where('id', $id)->first();
        if(Ablilty::isBrandCheck($request, $data->brand_id) === false)
            return $this->response(951);
        if(Ablilty::isEditAbleTime() === false)
            return $this->extendResponse(1500, '지금은 작업할 수 없습니다.');

        $res = $this->delete($this->finance_vans->where('id', $id));
        operLogging(HistoryType::DELETE, $this->target, $data, ['id' => $id], $data->nick_name);
        return $this->response($res, ['id'=>$id]);
    }
}
