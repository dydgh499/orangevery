<?php

namespace App\Http\Controllers\Manager\Service;

use App\Models\Service\MchtBlacklist;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Traits\StoresTrait;

use App\Http\Requests\Manager\BulkRegister\BulkMchtBlacklistRequest;
use App\Http\Requests\Manager\Service\MchtBlacklistRequest;
use App\Http\Requests\Manager\IndexRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * @group merchandise blacklists API
 *
 * 가맹점 블랙리스트 정보 API 입니다.
 */
class MchtBlacklistController extends Controller
{
    use ManagerTrait, ExtendResponseTrait, StoresTrait;
    protected $mcht_blacklists;

    public function __construct(MchtBlacklist $mcht_blacklists)
    {
        $this->mcht_blacklists = $mcht_blacklists;
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
        $search = $request->input('search', '');
        $query = $this->mcht_blacklists->where('brand_id', $request->user()->brand_id)
            ->where('block_reason', 'like', "%$search%");
        $data  = $this->getIndexData($request, $query);
        return $this->response(0, $data);
    }

    /**
     * 추가
     *
     * 마스터 이상 가능
     *
     */
    public function store(MchtBlacklistRequest $request)
    {
        $data = $request->data();
        $res = $this->mcht_blacklists->create($data);
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
        $data = $this->mcht_blacklists->where('id', $id)->first();
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
    public function update(MchtBlacklistRequest $request, int $id)
    {
        $data = $request->data();
        $res  = $this->mcht_blacklists->where('id', $id)->update($data);
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
        $res = $this->mcht_blacklists->where('id', $id)->delete();
        return $this->response($res ? 1 : 990, ['id'=>$id]);
    }

    public function all(Request $request)
    {
        $data = $this->mcht_blacklists->where('brand_id', $request->brand_id)->get();
        return $this->response(0, $data);
    }

    /**
     * 대량등록
     *
     */
    public function bulkRegister(BulkMchtBlacklistRequest $request)
    {
        $current = date('Y-m-d H:i:s');
        $datas = $request->data();

        $cards = $datas->map(function ($data) use($current) {
            $data['created_at'] = $current;
            $data['updated_at'] = $current;
            return $data;
        })->toArray();
        $res = $this->manyInsert($this->mcht_blacklists, $cards);
        return $this->response($res ? 1 : 990);
    }
}
