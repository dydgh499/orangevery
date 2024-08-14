<?php

namespace App\Http\Controllers\Manager\Service;

use App\Models\Service\Popup;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Requests\Manager\PopupRequest;
use App\Http\Requests\Manager\IndexRequest;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

/**
 * @group Popup API
 *
 * 팝업 API 입니다. 본사 이상 등급이 요구됩니다.
 */
class PopupController extends Controller
{
    use ManagerTrait, ExtendResponseTrait;
    protected $popups;

    public function __construct(Popup $popups)
    {
        $this->popups = $popups;
        $this->imgs = [
            'params'    => [],
            'cols'      => [],
            'folders'   => [],
            'sizes'     => [],
        ];
    }

    /**
     * 오픈기간에 포함된 팝업목록 조회
     */
    public function currently(IndexRequest $request)
    {
        $now = Carbon::now()->format('Y-m-d');
        $query  = $this->popups
            ->where('brand_id', $request->user()->brand_id)
            ->where('is_delete', false)
            ->where('open_s_dt', '<=', $now)
            ->where('open_e_dt', '>=', $now);
            
        $data   = $this->getIndexData($request, $query);
        return $this->response(0, $data);
    }

    /**
     * 목록조회
     *
     * @queryParam search string 검색어(팝업 제목)
     */
    public function index(IndexRequest $request)
    {
        $cols = ['operators.user_name', 'operators.profile_img', 'popups.id', 'popups.popup_title', 'popups.open_s_dt', 'popups.open_e_dt', 'popups.created_at', 'popups.updated_at'];
        $search = $request->input('search', '');
        $query  = $this->popups
            ->join('operators', 'popups.oper_id', '=', 'operators.id')
            ->where('popups.brand_id', $request->user()->brand_id)
            ->where('popups.is_delete', false)
            ->where('popups.popup_title', 'like', "%$search%");

        $data   = $this->getIndexData($request, $query, 'popups.id', $cols, 'popups.updated_at');
        return $this->response(0, $data);
    }

    /**
     * 추가
     *
     * 본사 이상 가능
     *
     * @urlParam id integer required 팝업 PK
     * @return \Illuminate\Http\Response
     */
    public function store(PopupRequest $request)
    {
        $data = $request->data();
        $data['oper_id'] = $request->user()->id;
        $data = $this->saveImages($request, $data, $this->imgs);
        $result = $this->popups->create($data);
        return $this->response($result ? 1 : 990, ['id'=>$result->id]);
    }

    /**
     * 단일조회
     *
     * 본사 이상 가능
     *
     * @urlParam id integer required 팝업 PK
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, int $id)
    {
        $data = $this->popups->where('id', $id)->first();
        return $this->response($data ? 0 : 1000, $data);
    }

    /**
     * 업데이트
     *
     * 본사 이상 가능
     *
     * @urlParam id integer required 팝업 PK
     * @return \Illuminate\Http\Response
     */
    public function update(PopupRequest $request, int $id)
    {
        $data = $request->data();
        $result = $this->popups->where('id', $id)->update($data);
        return $this->response($result ? 1 : 990, ['id'=>$id]);
    }

    /**
     * 단일삭제
     *
     * 본사 이상 가능
     *
     * @urlParam id integer required 팝업 PK
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, int $id)
    {
        $result = $this->delete($this->popups->where('id', $id));
        return $this->response($result);
    }
}
