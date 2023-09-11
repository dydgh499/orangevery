<?php

namespace App\Http\Controllers\Manager;

use App\Models\FinanceVan;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Traits\StoresTrait;

use App\Http\Requests\Manager\PayModuleRequest;
use App\Http\Requests\Manager\IndexRequest;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Enums\HistoryType;


class FinanceVanController extends Controller
{
    use ManagerTrait, ExtendResponseTrait, StoresTrait;
    protected $finance_vans;
    protected $target;

    public function __construct(FinanceVan $finance_vans)
    {
        $this->finance_vans = $finance_vans;
        $this->target = '금융 VAN';
        $this->imgs = [];
    }
    
    /**
     * 목록출력
     *
     * 본사 이상 가능
     */
    public function index(IndexRequest $request)
    {
        $query = $this->finance_vans->where('brand_id', $request->user()->brand_id);
        $data = $this->getIndexData($request, $query);
        return $this->response(0, $data);
    }

    /**
     * 추가
     *
     * 본사 이상 가능
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(OperatorReqeust $request)
    {
        $validated = $request->validate(['user_pw'=>'required']);
        $user = $request->data();
        $user = $this->saveImages($request, $user, $this->imgs);
        $user['user_pw'] = Hash::make($request->input('user_pw'));
        $res = $this->finance_vans->create($user);
        return $this->response($res ? 1 : 990, ['id'=>$res->id]);
    }

    /**
     * 단일조회
     *
     * 본사 이상 가능
     *
     * @urlParam id integer required 유저 PK
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, $id)
    {
        $data = $this->finance_vans->where('id', $id)->first();
        return $data ? $this->response(0, $data) : $this->response(1000);
    }

    /**
     * 업데이트
     *
     * 본사 이상 가능
     *
     * @urlParam id integer required 유저 PK
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(OperatorReqeust $request, $id)
    {
        $user = $request->data();
        $user = $this->saveImages($request, $user, $this->imgs);
        $res = $this->finance_vans->where('id', $id)->update($user);
        return $this->response($res ? 1 : 990);
    }

    /**
     * 단일삭제
     *
     * @urlParam id integer required 유저 PK
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, $id)
    {
        $res = $this->delete($this->finance_vans->where('id', $id));
        return $this->response($res);
    }
}
