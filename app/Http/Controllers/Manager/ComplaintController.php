<?php

namespace App\Http\Controllers\Manager;

use App\Models\Complaint;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Requests\Manager\ComplaintRequest;
use App\Http\Requests\Manager\IndexRequest;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * @group Post API
 *
 * 민원조회 API 입니다. 조회를 제외하고 마스터 이상권한이 요구됩니다.
 */
class ComplaintController extends Controller
{
    use ManagerTrait, ExtendResponseTrait;
    protected $complaints;

    public function __construct(Complaint $complaints)
    {
        $this->complaints = $complaints;
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
        $query  = $this->complaints
            ->where('brand_id', $request->user()->brand_id)
            ->where('tid', 'like', "%$search%");
        $query = $query->with(['mcht', 'pg']);

        $data = $this->getIndexData($request, $query);
        return $this->response(0, $data);

    }

    /**
     * 추가
     *
     * 마스터 이상 가능
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(ComplaintRequest $request)
    {
        $data = $request->data();
        $res  = $this->complaints->create($data);
        return $this->response($res ? 1 : 990);
    }

    /**
     * 단일조회
     *
     * 가맹점 이상 가능
     *
     * @urlParam id integer required 공지사항 PK
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $data = $this->complaints->where('id', $id)->first();
        return $this->response($data ? 0 : 1000, $data);
    }

    /**
     * 업데이트
     *
     * 마스터 이상 가능
     *
     * @urlParam id integer required 공지사항 PK
     * @return \Illuminate\Http\Response
     */
    public function update(ComplaintRequest $request, $id)
    {
        $data = $request->data();
        $res  = $this->complaints->where('id', $id)->update($data);
        return $this->response($res ? 1 : 990);
    }

    /**
     * 단일삭제
     *
     * 마스터 이상 가능
     *
     * @urlParam id integer required 공지사항 PK
     * @return \Illuminate\Http\Response
     */
    public function destroy(Post $notice)
    {
        $result = $this->delete($this->complaints->where('id', $id));
        return $this->response($result);
    }
}
