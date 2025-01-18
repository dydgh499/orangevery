<?php

namespace App\Http\Controllers\Manager\Service;

use App\Models\Service\DifferentSettlementInfo;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Traits\StoresTrait;

use App\Http\Requests\Manager\Service\DifferentSettlementInfoRequest;
use App\Http\Requests\Manager\IndexRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * @group different settlement info API
 *
 * 차액정산정보 API 입니다.
 */
class DifferentSettlementInfoController extends Controller
{
    use ManagerTrait, ExtendResponseTrait, StoresTrait;
    protected $different_settlement_infos;

    public function __construct(DifferentSettlementInfo $different_settlement_infos)
    {
        $this->different_settlement_infos = $different_settlement_infos;
    }

    /**
     * 목록출력
     *
     * 가맹점 이상 가능
     *
     * @queryParam search string 검색어(제목)
     */
    public function index(Request $request)
    {
        $data = $this->different_settlement_infos->where('brand_id', $request->user()->brand_id)->get(['pg_type', 'id']);
        return $this->response(0, $data);

    }

    /**
     * 추가
     *
     * 본사 이상 가능
     *
     */
    public function store(DifferentSettlementInfoRequest $request)
    {
        $data = $request->data();
        $res = $this->different_settlement_infos->create($data);
        return $this->response($res ? 1 : 990, ['id'=>$res->id]);
    }

    /**
     * 단일조회
     *
     * 가맹점 이상 가능
     *
     * @urlParam id integer required 정기등록카드 PK
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $data = $this->different_settlement_infos->where('id', $id)->first();
        return $this->response($data ? 0 : 1000, $data);
    }

    /**
     * 업데이트
     *
     * 본사 이상 가능
     *
     * @urlParam id integer required 정기등록카드 PK
     * @return \Illuminate\Http\Response
     */
    public function update(DifferentSettlementInfoRequest $request, int $id)
    {
        $data = $request->data();
        $res  = $this->different_settlement_infos->where('id', $id)->update($data);
        return $this->response($res ? 1 : 990, ['id'=>$id]);
    }

    /**
     * 단일삭제
     *
     * 본사 이상 가능
     *
     * @urlParam id integer required 정기등록카드 PK
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $res = $this->different_settlement_infos->where('id', $id)->delete();
        return $this->response($res ? 1 : 990, ['id'=>$id]);
    }
}
