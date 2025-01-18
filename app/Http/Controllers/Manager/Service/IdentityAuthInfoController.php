<?php

namespace App\Http\Controllers\Manager\Service;

use App\Models\Service\IdentityAuthInfo;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Traits\StoresTrait;

use App\Http\Controllers\Ablilty\Ablilty;
use App\Http\Controllers\Ablilty\EditAbleWorkTime;

use App\Http\Requests\Manager\Service\IdentityAuthInfoRequest;
use App\Http\Requests\Manager\IndexRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * @group before brand info API
 *
 * 이전 운영사 정보 API 입니다.
 */
class IdentityAuthInfoController extends Controller
{
    use ManagerTrait, ExtendResponseTrait, StoresTrait;
    protected $identity_auth_infos;

    public function __construct(IdentityAuthInfo $identity_auth_infos)
    {
        $this->identity_auth_infos = $identity_auth_infos;
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
     * 본사 이상 가능
     *
     */
    public function store(IdentityAuthInfoRequest $request)
    {
        if(EditAbleWorkTime::validate() === false)
            return $this->extendResponse(1500, '지금은 작업할 수 없습니다.');

        $res = $this->identity_auth_infos->create($request->data());
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
        $data = $this->identity_auth_infos->where('id', $id)->first();
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
    public function update(IdentityAuthInfoRequest $request, int $id)
    {
        if(EditAbleWorkTime::validate() === false)
            return $this->extendResponse(1500, '지금은 작업할 수 없습니다.');

        $data = $this->identity_auth_infos->encrypt($request->data());
        $res  = $this->identity_auth_infos->where('id', $id)->update($data);
        return $this->response($res ? 1 : 990, ['id'=>$id, 'brand_id'=>$data['brand_id']]);
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
        if(EditAbleWorkTime::validate() === false)
            return $this->extendResponse(1500, '지금은 작업할 수 없습니다.');

        $res = $this->identity_auth_infos->where('id', $id)->delete();
        return $this->response($res ? 1 : 990, ['id'=>$id]);
    }
}
