<?php

namespace App\Http\Controllers\Manager;

use App\Models\DifferentSettlementInfo;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Traits\StoresTrait;

use App\Http\Requests\Manager\DifferentSettlementInfoRequest;
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
    protected $different_settlement_Infos;

    public function __construct(DifferentSettlementInfo $different_settlement_Infos)
    {
        $this->different_settlement_Infos = $different_settlement_Infos;
    }

    /**
     * 목록출력
     *
     * 가맹점 이상 가능
     *
     * @queryParam search string 검색어(제목)
     */
    public function index(IndexRequest $request)
    {

    }

    /**
     * 추가
     *
     * 마스터 이상 가능
     *
     */
    public function store(DifferentSettlementInfoRequest $request)
    {
        $data = $request->data();
        $res = $this->different_settlement_Infos->create($data);
        return $this->response($res ? 1 : 990, ['id'=>$res->id, 'brand_id'=>$data['brand_id']]);
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
        $data = $this->different_settlement_Infos->where('id', $id)->first();
        return $this->response($data ? 0 : 1000, $data);
    }

    /**
     * 업데이트
     *
     * 마스터 이상 가능
     *
     * @urlParam id integer required 정기등록카드 PK
     * @return \Illuminate\Http\Response
     */
    public function update(DifferentSettlementInfoRequest $request, $id)
    {
        $data = $request->data();
        $res  = $this->different_settlement_Infos->where('id', $id)->update($data);
        return $this->response($res ? 1 : 990, ['id'=>$id, 'brand_id'=>$data['brand_id']]);
    }

    /**
     * 단일삭제
     *
     * 마스터 이상 가능
     *
     * @urlParam id integer required 정기등록카드 PK
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $res = $this->different_settlement_Infos->where('id', $id)->delete();
        return $this->response($res ? 1 : 990, ['id'=>$id]);
    }
}
