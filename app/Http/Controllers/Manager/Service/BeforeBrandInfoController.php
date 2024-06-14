<?php

namespace App\Http\Controllers\Manager\Service;

use App\Models\Service\BeforeBrandInfo;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Traits\StoresTrait;

use App\Http\Controllers\Ablilty\Ablilty;
use App\Http\Requests\Manager\BulkRegister\BulkRegularCardRequest;
use App\Http\Requests\Manager\Service\BeforeBrandInfoRequest;
use App\Http\Requests\Manager\IndexRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * @group before brand info API
 *
 * 이전 운영사 정보 API 입니다.
 */
class BeforeBrandInfoController extends Controller
{
    use ManagerTrait, ExtendResponseTrait, StoresTrait;
    protected $before_brand_infos;

    public function __construct(BeforeBrandInfo $before_brand_infos)
    {
        $this->before_brand_infos = $before_brand_infos;
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
    public function store(BeforeBrandInfoRequest $request)
    {
        if(Ablilty::isEditAbleTime() === false)
            return $this->extendResponse(1500, '지금은 작업할 수 없습니다.');

        $data = $request->data();
        $res = $this->before_brand_infos->create($data);
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
        $data = $this->before_brand_infos->where('id', $id)->first();
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
    public function update(BeforeBrandInfoRequest $request, int $id)
    {
        if(Ablilty::isEditAbleTime() === false)
            return $this->extendResponse(1500, '지금은 작업할 수 없습니다.');

        $data = $request->data();
        $res  = $this->before_brand_infos->where('id', $id)->update($data);
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
        if(Ablilty::isEditAbleTime() === false)
            return $this->extendResponse(1500, '지금은 작업할 수 없습니다.');
        $res = $this->before_brand_infos->where('id', $id)->delete();
        return $this->response($res ? 1 : 990, ['id'=>$id]);
    }
}
