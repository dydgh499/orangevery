<?php

namespace App\Http\Controllers\Manager;

use App\Models\HeadOfficeAccount;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;

use App\Http\Requests\Manager\HeadOfficeAccountRequest;
use App\Http\Requests\Manager\IndexRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HeadOfficeAccountController extends Controller
{
    use ManagerTrait, ExtendResponseTrait;
    protected $head_office_accounts;

    public function __construct(HeadOfficeAccount $head_office_accounts)
    {
        $this->head_office_accounts = $head_office_accounts;
    }

    /**
     * 목록출력
     *
     * 가맹점 이상 가능
     *
     */
    public function index(Request $request)
    {
        $request->merge([
            'page' => 1,
            'page_size' => 99999999,
        ]);
        $query  = $this->head_office_accounts->where('brand_id', $request->user()->brand_id);
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
    public function store(HeadOfficeAccountRequest $request)
    {
        $data = $request->data();
        $res = $this->head_office_accounts->create($data);
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
        $data = $this->head_office_accounts->where('id', $id)->first();
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
    public function update(HeadOfficeAccountRequest $request, $id)
    {
        $data = $request->data();
        $res  = $this->head_office_accounts->where('id', $id)->update($data);
        return $this->response($res ? 1 : 990, ['id'=>$id]);
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
        $res = $this->head_office_accounts->where('id', $id)->delete();
        return $this->response($res ? 1 : 990, ['id'=>$id]);
    }
}
