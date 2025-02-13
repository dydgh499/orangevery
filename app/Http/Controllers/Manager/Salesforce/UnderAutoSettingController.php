<?php

namespace App\Http\Controllers\Manager\Salesforce;

use App\Models\Salesforce\UnderAutoSetting;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;

use App\Http\Requests\Manager\Salesforce\UnderAutoSettingRequest;
use App\Http\Requests\Manager\IndexRequest;
use App\Enums\HistoryType;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * @group Under Auto Setting API
 *
 * 영업점 수수료율 세팅 API 입니다.
 */
class UnderAutoSettingController extends Controller
{
    use ManagerTrait, ExtendResponseTrait;
    protected $under_auto_settings;

    public function __construct(UnderAutoSetting $under_auto_settings)
    {
        $this->under_auto_settings = $under_auto_settings;
        $this->imgs = [];
    }
    
    /**
     * 목록출력
     *
     * 영업점 이상 가능
     */
    public function index(IndexRequest $request)
    {
        $data = [];
        return $this->response(0, $data);
    }

    /**
     * 추가
     *
     * 대리점 이상 가능
     *
     */
    public function store(UnderAutoSettingRequest $request)
    {
        $data = $request->data();
        $res = $this->under_auto_settings->create($data);
        return $this->response($res ? 1 : 990, ['id'=>$res->id, 'sales_id'=>$data['sales_id']]);
    }

    /**
     * 단일조회
     *
     * 영업점 이상 가능
     *
     * @urlParam id integer required 유저 PK
     */
    public function show(Request $request, int $id)
    {
        $data = $this->under_auto_settings->where('id', $id)->first();
        return $data ? $this->response(0, $data) : $this->response(1000);
    }

    /**
     * 업데이트
     *
     * 영업점 이상 가능
     *
     * @urlParam id integer required 유저 PK
     */
    public function update(UnderAutoSettingRequest $request, int $id)
    {
        $data = $request->data();
        $res = $this->under_auto_settings->where('id', $id)->update($data);
        return $this->response($res ? 1 : 990, ['id'=>$id, 'sales_id'=>$data['sales_id']]);
    }

    /**
     * 단일삭제
     *
     * @urlParam id integer required 유저 PK
     */
    public function destroy(Request $request, int $id)
    {
        $res = $this->under_auto_settings->where('id', $id)->delete();
        return $this->response($res ? 1 : 990, ['id'=>$id]);
    }
}
