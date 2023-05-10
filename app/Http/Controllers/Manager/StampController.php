<?php

namespace App\Http\Controllers\Manager;

use App\Models\Stamp;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Requests\Manager\StampForm;
use App\Http\Requests\Manager\IndexForm;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * @group Stamp API
 *
 * 스탬프관리 메뉴에서 사용될 API 입니다. 조회를 제외하고 마스터 이상권한이 요구됩니다.
 */
class StampController extends Controller
{
    use ManagerTrait, ExtendResponseTrait;
    protected $stamps;

    public function __construct(Stamp $stamps)
    {
        $this->stamps = $stamps;
    }

    /**
     * 목록출력
     *
     * 가맹점 이상 가능
     *
     * @queryParam search string 검색어(유저 ID, 가맹점 명)
     */
    public function index(IndexForm $request)
    {
        $search = $request->input('search', '');
        $query = $this->stamps->merchandise()
            ->where('users.brand_id', $request->user()->brand_id);

        $query = $query->where(function ($query) use ($search) {
            $query->where('users.user_name', 'like', "%$search%")
                ->orWhere('merchandises.mcht_name', 'like', "%$search%");
        });
        $data = $this->getIndexData($request, $query, 'orders.id', ['stamps.*', 'users.user_name, merchandises.mcht_name'], 'stamps.created_at');
        return $this->response(0, $data);
    }

    /**
     * 추가
     *
     * 마스터 이상 가능
     *
     * @bodyParam user_id integer required 유저 PK Example: 1
     * @bodyParam mcht_id integer required 가맹점 PK Example: 1
     * @bodyParam use_status string required 사용 상태(미사용=0, 사용=1) Example: 1
     * @bodyParam valid_s_dt string required 유통기한 시작일 Example: 2022-01-01
     * @bodyParam valid_e_dt string required 유통기한 종료일 Example: 2024-01-01
     * @bodyParam use_dt string 사용일(use_status가 사용일때 사용) Example: 2022-05-05
     * @return \Illuminate\Http\Response
     */
    public function store(StampForm $request)
    {
        $data = $request->data();
        $result = $this->stamps->create($data);
        return $this->response($result ? 1 : 990);
    }

    /**
     * 단일조회
     *
     * @urlParam id integer required 스탬프 PK
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $data = $this->stamps->where('id', $id)->first();
        return $this->response($data ? 0 : 1000, $data);
    }

    /**
     * 업데이트
     *
     * 마스터 이상 가능
     *
     * @urlParam id integer required 스탬프 PK
     * @bodyParam user_id integer required 유저 PK Example: 1
     * @bodyParam mcht_id integer required 가맹점 PK Example: 1
     * @bodyParam use_status string required 사용 상태(미사용=0, 사용=1) Example: 1
     * @bodyParam valid_s_dt string required 유통기한 시작일 Example: 2022-01-01
     * @bodyParam valid_e_dt string required 유통기한 종료일 Example: 2024-01-01
     * @bodyParam use_dt string 사용일(use_status가 사용일때 사용) Example: 2022-05-05
     * @return \Illuminate\Http\Response
     */
    public function update(StampForm $request, $id)
    {
        $data = $request->data();
        $result = $this->stamps->where('id', $id)->update($data);
        return $this->response($result ? 1 : 990);
    }

    /**
     * 단일삭제
     *
     * @urlParam id integer required 스탬프 PK
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $result = $this->delete($this->stamps->where('id', $id), []);
        return $this->response($result);
    }
}
