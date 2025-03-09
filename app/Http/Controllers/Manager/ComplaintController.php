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
 * @group Complaint API
 *
 * 민원조회 API 입니다. 조회를 제외하고 가맹점 이상권한이 요구됩니다.
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
            ->join('merchandises', 'complaints.mcht_id', '=', 'merchandises.id')
            ->where('merchandises.brand_id', $request->user()->brand_id)
            ->where('complaints.is_delete', false)
            ->where('merchandises.is_delete', false)
            ->where(function ($query) use ($search) {
                return $query->where('complaints.tid', 'like', "%$search%")
                    ->orWhere('complaints.appr_num', 'like', "%$search%")
                    ->orWhere('complaints.cust_name', 'like', "%$search%")
                    ->orWhere('merchandises.mcht_name', 'like', "%$search%");
            });

        $query = globalSalesFilter($query, $request, 'merchandises');
        $query = globalAuthFilter($query, $request, 'merchandises');
    
        if($request->history_type !== null)
            $query->where('complaints.type', $request->history_type);

        $data = $this->getIndexData($request, $query, 'complaints.id', ['complaints.*', 'merchandises.mcht_name'], 'complaints.created_at');
        return $this->response(0, $data);
    }

    /**
     * 추가
     *
     * 가맹점 이상 가능
     *
     */
    public function store(ComplaintRequest $request)
    {
        $data = $request->data();
        $res  = $this->complaints->create($data);
        return $this->response($res ? 1 : 990, ['id'=>$res->id]);
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
     * 가맹점 이상 가능
     *
     * @urlParam id integer required 공지사항 PK
     * @return \Illuminate\Http\Response
     */
    public function update(ComplaintRequest $request, int $id)
    {
        $data = $request->data();
        $res  = $this->complaints->where('id', $id)->update($data);
        return $this->response($res ? 1 : 990, ['id'=>$id]);
    }

    /**
     * 단일삭제
     *
     * 가맹점 이상 가능
     *
     * @urlParam id integer required 공지사항 PK
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $res = $this->delete($this->complaints->where('id', $id));
        return $this->response($res ? 1 : 990, ['id'=>$id]);
    }
}
