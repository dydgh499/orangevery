<?php

namespace App\Http\Controllers\Manager\Merchandise;

use App\Models\Merchandise;
use App\Models\Merchandise\NotiUrl;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Traits\StoresTrait;

use App\Http\Controllers\Ablilty\ActivityHistoryInterface;

use App\Http\Requests\Manager\IndexRequest;
use App\Http\Requests\Manager\Merchandise\NotiRequest;
use App\Http\Controllers\Utils\ChartFormat;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * @group Noti URL API
 *
 * 노티 URL API입니다.
 */
class NotiUrlController extends Controller
{
    use ManagerTrait, ExtendResponseTrait, StoresTrait;
    protected $noti_urls, $merchandises;

    public function __construct(NotiUrl $noti_urls, Merchandise $merchandises)
    {
        $this->noti_urls = $noti_urls;
        $this->merchandises = $merchandises;
        $this->target = '노티 URL';
        $this->imgs = [];
    }
    
    /**
     * 차트 데이터 출력
     *
     * 가맹점 이상 가능
     */
    public function chart(Request $request)
    {
        return $this->response(0, []);
    }


    public function commonSelect($request)
    {
        $search = $request->input('search', '');
        $query = $this->noti_urls
                ->join('merchandises', 'noti_urls.mcht_id', '=', 'merchandises.id')
                ->leftJoin('payment_modules', 'noti_urls.pmod_id', '=', 'payment_modules.id')
                ->where('merchandises.brand_id', $request->user()->brand_id)
                ->where(function ($query) {
                    return $query->whereColumn('noti_urls.pmod_id', 'payment_modules.id')
                        ->orWhere('noti_urls.pmod_id', -1);
                })
                ->where('merchandises.is_delete', false)
                ->where('noti_urls.is_delete', false)
                ->where(function ($query) use ($search) {
                    return $query->where('merchandises.mcht_name', 'like', "%$search%")
                        ->orWhere('noti_urls.send_url', 'like', "%$search%")
                        ->orWhere('payment_modules.note', 'like', "%$search%");
                });
                
        $query = globalAuthFilter($query, $request, 'merchandises');        
        if($request->mcht_id)
            $query = $query->where('noti_urls.mcht_id', $request->mcht_id);
        return $query;
    }

    /**
     * 목록출력
     *
     * 가맹점 이상 가능
     */
    public function index(IndexRequest $request)
    {
        $cols  = ['noti_urls.*', 'merchandises.mcht_name', 'payment_modules.note as pmod_note'];
        $query = $this->commonSelect($request);
        $data = $this->getIndexData($request, $query, 'noti_urls.id', $cols, 'noti_urls.created_at');
        return $this->response(0, $data);
    }

    /**
     * 추가
     *
     * 대리점 이상 가능
     *
     */
    public function store(NotiRequest $request)
    {
        $data = $request->data();
        $res = app(ActivityHistoryInterface::class)->add($this->target, $this->noti_urls, $data, 'note');
        if($res)
            return $this->response(1, ['id' => $res->id, 'mcht_id' => $data['mcht_id']]);    
        else
            return $this->response(990, []);
    }

    /**
     * 단일조회
     *
     * 가맹점 이상 가능
     *
     * @urlParam id integer required 유저 PK
     */
    public function show(Request $request, int $id)
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
     */
    public function update(NotiRequest $request, int $id)
    {
        $data = $request->data();
        $query = $this->noti_urls->where('id', $id);
        $row = app(ActivityHistoryInterface::class)->update($this->target, $query, $data, 'note');
        if($row)
            return $this->response(1, ['id' => $id]);
        else
            return $this->response(990);
    }

    /**
     * 단일삭제
     *
     * @urlParam id integer required 유저 PK
     */
    public function destroy(Request $request, int $id)
    {
        $query = $this->noti_urls->where('id', $id);
        $row = app(ActivityHistoryInterface::class)->destory($this->target, $query, 'note');
        return $this->response(1, ['id' => $id]);    
    }
}
