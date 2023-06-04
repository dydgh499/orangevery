<?php

namespace App\Http\Controllers\Manager;

use App\Models\PaymentModule;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Requests\Manager\PayModuleRequest;
use App\Http\Requests\Manager\IndexRequest;
use App\Http\Traits\StoresTrait;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PaymentModuleController extends Controller
{
    use ManagerTrait, ExtendResponseTrait, StoresTrait;
    protected $payModules;

    public function __construct(PaymentModule $payModules)
    {
        $this->payModules = $payModules;
        $this->imgs = [];
    }

    /**
     * 목록출력
     *
     * 가맹점 이상 가능
     *
     * @queryParam search string 검색어(mid, tid)
     * @queryParam module_type integer 모듈타입(0,1,2,3,4)
     */
    public function index(IndexRequest $request)
    {
        $module_type = $request->input('module_type', '');
        $search = $request->input('search', '');
        $query  = $this->payModules
            ->where('brand_id', $request->user()->brand_id);

        if($module_type != '')
            $query = $query->where('module_type', $module_type);

        $query = $query->where(function ($query) use ($search) {
            return $query->where('mid', 'like', "%$search%")
                ->orWhere('tid', 'like', "%$search%");
        });

        if($request->has('mcht_id'))
            $query = $query->where('mcht_id', $request->mcht_id);

        $data = $this->getIndexData($request, $query);
        return $this->response(0, $data);
    }

    /**
     * 추가
     *
     * 대리점 이상 가능
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(PayModuleRequest $request)
    {
        if($request->user()->tokenCan(10))
        {
            $item = $request->data();
            $res = $this->payModules->create($item);
            return $this->response($res ? 1 : 990);
        }
        else
            return $this->response(951);
    }

    /**
     * 단일조회
     *
     * 가맹점 이상 가능
     *
     * @urlParam id integer required 유저 PK
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, $id)
    {
        if($this->authCheck($request->user(), $id, 15))
        {
            $data = $this->payModules->where('id', $id)->first();
            return $data ? $this->response(0, $data) : $this->response(1000);
        }
        else
            return $this->response(951);
    }

    /**
     * 업데이트
     *
     * 가맹점 이상 가능
     *
     * @urlParam id integer required 유저 PK
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(PayModuleRequest $request, $id)
    {
        if($this->authCheck($request->user(), $id, 15))
        {
            $data = $request->data();
            $res = $this->payModules->where('id', $id)->update($data);
            return $this->response($res ? 1 : 990);
        }
        else
            return $this->response(951);
    }

    /**
     * 단일삭제
     *
     * @urlParam id integer required 유저 PK
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, $id)
    {
        if($this->authCheck($request->user(), $id, 15))
        {
            $res = $this->delete($this->payModules->where('id', $id));
            return $this->response($res);
        }
        else
            return $this->response(951);
    }
}
