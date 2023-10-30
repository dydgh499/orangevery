<?php

namespace App\Http\Controllers\Manager;

use App\Models\RegularCreditCard;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;

use App\Http\Requests\Manager\RegularCreditCardRequest;
use App\Http\Requests\Manager\IndexRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RegularCreditCardController extends Controller
{
    use ManagerTrait, ExtendResponseTrait;
    protected $cards;

    public function __construct(RegularCreditCard $cards)
    {
        $this->cards = $cards;
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(RegularCreditCardRequest $request)
    {
        $data = $request->data();
        $res = $this->cards->create($data);
        return $this->response($res ? 1 : 990, ['id'=>$res->id, 'mcht_id'=>$data['mcht_id']]);
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
        $data = $this->cards->where('id', $id)->first();
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
    public function update(RegularCreditCardRequest $request, $id)
    {
        $data = $request->data();
        $res  = $this->cards->where('id', $id)->update($data);
        return $this->response($res ? 1 : 990, ['id'=>$id, 'mcht_id'=>$data['mcht_id']]);
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
        $res = $this->cards->where('id', $id)->delete();
        return $this->response($res ? 1 : 990, ['id'=>$id]);
    }
}
