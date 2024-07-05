<?php

namespace App\Http\Controllers\Manager\Merchandise;

use App\Models\Merchandise;
use App\Models\Merchandise\NotiUrl;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Traits\StoresTrait;

use App\Http\Requests\Manager\IndexRequest;
use App\Http\Requests\Manager\Merchandise\NotiRequest;
use App\Http\Requests\Manager\BulkRegister\BulkNotiUrlRequest;
use App\Enums\HistoryType;

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
     * 목록출력
     *
     * 가맹점 이상 가능
     */
    public function index(IndexRequest $request)
    {
        $cols = ['noti_urls.*', 'merchandises.mcht_name'];
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
     */
    public function store(NotiRequest $request)
    {
        $data = $request->data();
        $res = $this->noti_urls->create($data);//use_noti
        if($res)
        {
            $this->merchandises->where('id', $data['mcht_id'])->update(['use_noti'=>true]);
            operLogging(HistoryType::CREATE, $this->target, [], $data, $data['note']);
        }
        return $this->response($res ? 1 : 990, ['id'=>$res->id, 'mcht_id'=>$data['mcht_id']]);
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
        $before = $this->noti_urls->where('id', $id)->first();
        $res = $this->noti_urls->where('id', $id)->update($data);
        if($res)
        {
            operLogging(HistoryType::UPDATE, $this->target, $before, $data, $data['note']);
        }
        return $this->response($res ? 1 : 990, ['id'=>$id, 'mcht_id'=>$data['mcht_id']]);
    }

    /**
     * 단일삭제
     *
     * @urlParam id integer required 유저 PK
     */
    public function destroy(Request $request, int $id)
    {
        $res = $this->delete($this->noti_urls->where('id', $id));    
        $data = ['id'=> $id];
        if($res)
        {
            $noti   = $this->noti_urls->where('id', $id)->first();
            $count  = $this->noti_urls->where('mcht_id', $noti->mcht_id)->where('is_delete', false)->count();
            if($count == 0)
                $this->merchandises->where('id', $noti->mcht_id)->update(['use_noti'=>false]);

            $data['mcht_id'] = $noti->mcht_id;
            operLogging(HistoryType::DELETE, $this->target, $noti, ['id' => $id], $noti->note);
        }
        return $this->response($res, $data);
    }

    /**
     * 대량등록
     *
     * 운영자 이상 가능
     */
    public function bulkRegister(BulkNotiUrlRequest $request)
    {
        $current = date('Y-m-d H:i:s');
        $datas = $request->data();

        $noti_urls = $datas->map(function ($data) use($current) {
            $data['created_at'] = $current;
            $data['updated_at'] = $current;
            return $data;
        })->toArray();
        $res = $this->manyInsert($this->noti_urls, $noti_urls);
        return $this->response($res ? 1 : 990);
    }
}
