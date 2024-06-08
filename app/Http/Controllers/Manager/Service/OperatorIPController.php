<?php

namespace App\Http\Controllers\Manager\Service;

use App\Models\Service\OperatorIP;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;

use App\Http\Requests\Manager\Service\OperatorIPRequest;
use App\Http\Requests\Manager\IndexRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * @group operator IP API
 *
 * 운영자 허용 IP API 입니다.
 */
class OperatorIPController extends Controller
{
    use ManagerTrait, ExtendResponseTrait;
    protected $operator_ips;

    public function __construct(OperatorIP $operator_ips)
    {
        $this->operator_ips = $operator_ips;
    }

    /**
     * 목록출력
     *
     * @queryParam search string 검색어(제목)
     */
    public function index(IndexRequest $request)
    {
        $search = $request->input('search', '');
        $query = $this->operator_ips->where('brand_id', $request->user()->brand_id);
        $data  = $this->getIndexData($request, $query);
        return $this->response(0, $data);
    }

    /**
     * 추가
     *
     */
    public function store(OperatorIPRequest $request)
    {
        $data = $request->data();
        $res = $this->operator_ips->create($data);
        return $this->response($res ? 1 : 990, ['id'=>$res->id, 'brand_id'=>$data['brand_id']]);
    }

    /**
     * 단일조회
     *
     * @urlParam id integer required PK
     * @return \Illuminate\Http\Response
     */
    public function show(int $id)
    {
        $data = $this->operator_ips->where('id', $id)->first();
        return $this->response($data ? 0 : 1000, $data);
    }

    /**
     * 업데이트
     *
     * @urlParam id integer required PK
     * @return \Illuminate\Http\Response
     */
    public function update(OperatorIPRequest $request, int $id)
    {
        $data = $request->data();
        $res  = $this->operator_ips->where('id', $id)->update($data);
        return $this->response($res ? 1 : 990, ['id'=>$id, 'brand_id'=>$data['brand_id']]);
    }

    /**
     * 단일삭제
     *
     * @urlParam id integer required PK
     * @return \Illuminate\Http\Response
     */
    public function destroy(int $id)
    {
        $res = $this->operator_ips->where('id', $id)->delete();
        return $this->response($res ? 1 : 990, ['id'=>$id]);
    }
}
