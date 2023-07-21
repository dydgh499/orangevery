<?php

namespace App\Http\Controllers\Manager;

use App\Models\NotiUrl;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;

use App\Http\Requests\Manager\NotiRequest;
use App\Http\Requests\Manager\IndexRequest;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotiUrlController extends Controller
{
    use ManagerTrait, ExtendResponseTrait;
    protected $noti_urls;

    public function __construct(NotiUrl $noti_urls)
    {
        $this->noti_urls = $noti_urls;
        $this->imgs = [];
    }
    
    /**
     * 목록출력
     *
     * 가맹점 이상 가능
     */
    public function index(IndexRequest $request)
    {
        $cols = ['noti_urls.*', 'merchandises.mcht_name'];
        $query = $this->noti_urls
                ->join('merchandises', 'noti_urls.mcht_id', '=', 'merchandises.id')
                ->where('merchandises.brand_id', $request->user()->brand_id)
                ->where('merchandises.is_delete', false)
                ->where('noti_urls.is_delete', false);
        $query = globalSalesFilter($query, $request, 'merchandises');
        $query = globalAuthFilter($query, $request, 'merchandises');
        
        if($request->mcht_id)
            $query = $query->where('noti_urls.mcht_id', $request->mcht_id);

        $data = $this->getIndexData($request, $query, 'noti_urls.id', $cols, 'noti_urls.created_at');
        return $this->response(0, $data);
    }

    /**
     * 추가
     *
     * 대리점 이상 가능
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(NotiRequest $request)
    {
        $user = $request->data();
        $res = $this->noti_urls->create($user);
        return $this->response($res ? 1 : 990);
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
        $data = $this->noti_urls->where('id', $id)->first();
        return $data ? $this->response(0, $data) : $this->response(1000);
    }

    /**
     * 업데이트
     *
     * 가맹점 이상 가능
     *
     * @urlParam id integer required 유저 PK
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(NotiRequest $request, $id)
    {
        $data = $request->data();
        $res = $this->noti_urls->where('id', $id)->update($data);
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
        $res = $this->delete($this->merchandises->where('id', $id));
        return $this->response($res);
    }
}
