<?php

namespace App\Http\Controllers\Manager\Service;

use App\Models\Service\HeadOfficeAccount;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;

use App\Http\Requests\Manager\Service\HeadOfficeAccountRequest;
use App\Http\Requests\Manager\IndexRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * @group Head Office Account API
 *
 * 본사 지정계좌 API입니다.
 */
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
     */
    public function store(HeadOfficeAccountRequest $request)
    {
        /*
        $data = $request->data();
        $res = $this->head_office_accounts->create($data);
        return $this->response($res ? 1 : 990, ['id'=>$res->id]);
        */
        return $this->extendResponse(1999, '지금은 추가할 수 없습니다,');
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
        /*
        $data = $request->data();
        $res  = $this->head_office_accounts->where('id', $id)->update($data);
        return $this->response($res ? 1 : 990, ['id'=>$id]);
        */
        return $this->extendResponse(1999, '지금은 업데이트할 수 없습니다.');
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
