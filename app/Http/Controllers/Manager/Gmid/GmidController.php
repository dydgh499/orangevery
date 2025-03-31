<?php

namespace App\Http\Controllers\Manager\Gmid;

use App\Http\Controllers\Ablilty\Ablilty;
use App\Http\Controllers\Ablilty\EditAbleWorkTime;

use App\Models\Gmid;
use App\Http\Traits\StoresTrait;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;

use App\Http\Requests\Manager\GmidRequest;
use App\Http\Requests\Manager\IndexRequest;

use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Enums\HistoryType;

class GmidController extends Controller
{
    use ManagerTrait, ExtendResponseTrait, StoresTrait;
    protected $gmids, $imgs;

    public function __construct(Gmid $gmids)
    {
        $this->gmids = $gmids;
        $this->target = 'GMID';
        $this->imgs = [
            'params' => [
                'profile_file',
            ],
            'cols' => [
                'profile_img',
            ],
            'folders' => [
                'profiles',
            ],
            'sizes' => [
                120,
            ],
        ];
    }

    /**
     * 목록출력
     *
     * 운영자 이상 가능
     *
     * @queryParam search string 검색어(아아디)
     */
    public function index(IndexRequest $request)
    {
        if(Ablilty::isOperator($request))
        {
            $search = $request->input('search', '');
            $query  = $this->gmids
                ->where('brand_id', $request->user()->brand_id)
                ->where('is_delete', false)
                ->where(function ($query) use ($search) {
                    return $query->where('g_mid', 'like', "%$search%");
            });
    
            $data = $this->getIndexData($request, $query);
            return $this->response(0, $data);    
        }
        else
            return $this->response(951);
    }

    /**
     * 추가
     *
     */
    public function store(GmidRequest $request)
    {
        if(Ablilty::isOperator($request))
        {
            if(EditAbleWorkTime::validate() === false)
                return $this->extendResponse(1500, '지금은 작업할 수 없습니다.');
            if($this->isExistMutual($this->gmids, $request->user()->brand_id, 'g_mid', $request->g_mid))
                return $this->extendResponse(1001, __("validation.already_exsit", ['attribute'=>'GMID']));
            if($this->isExistUserName($request->user()->brand_id, $request->user_name))
                return $this->extendResponse(1001, __("validation.already_exsit", ['attribute'=>'아이디']));
            else
            {
                $current = date("Y-m-d H:i:s");
                $data = $request->data();
                $data = $this->saveImages($request, $data, $this->imgs);
                $data['brand_id']   = $request->user()->brand_id;
                $data['user_pw']    = Hash::make($request->user_pw.$current);
                $data['created_at'] = $current;
                $res  = $this->gmids->create($data);
                operLogging(HistoryType::CREATE, $this->target, [], $data, $data['g_mid']);
                return $this->response($res ? 1 : 990, ['id' => $res->id]);    
            }
        }
        else
            return $this->response(951);
    }

    /**
     * 단일조회
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if(Ablilty::isMyGmid($request, $id) || Ablilty::isOperator($request))
        {
            $data = $this->gmids->where('id', $id)->first();
            return $this->response($data ? 0 : 1000, $data);    
        }
        else
            return $this->response(951);
    }

    /**
     * 업데이트
     *
     * @return \Illuminate\Http\Response
     */
    public function update(GmidRequest $request, $id)
    {
        if(Ablilty::isOperator($request))
        {
            $user = $this->gmids->where('id', $id)->first();
            if(EditAbleWorkTime::validate() === false)
                return $this->extendResponse(1500, '지금은 작업할 수 없습니다.');
            if($this->isExistMutual($this->gmids->where('id', '!=', $id), $request->user()->brand_id, 'g_mid', $request->g_mid))
                return $this->extendResponse(1001, '이미 존재하는 GMID 입니다.');
            if($user->user_name !== $request->user_name && $this->isExistUserName($request->user()->brand_id, $request->user_name))
                return $this->extendResponse(1001, __("validation.already_exsit", ['attribute'=>'아이디']));
            else
            {
                $data = $request->data();
                $data = $this->saveImages($request, $data, $this->imgs);
                $res  = $this->gmids->where('id', $id)->update($data);
                operLogging(HistoryType::UPDATE, $this->target, $user, $data, $data['g_mid']);
                return $this->response($res ? 1 : 990, ['id' => $id]);
            }
        }
        else
            return $this->response(951);
    }

    /**
     * 단일삭제
     *
     * 운영자 이상 가능
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, int $id)
    {
        if(Ablilty::isOperator($request))
        {
            $user = $this->gmids->where('id', $id)->first();
            if(EditAbleWorkTime::validate() === false)
                return $this->extendResponse(1500, '지금은 작업할 수 없습니다.');
            else
            {
                $res = $this->delete($this->gmids->where('id', $id));
                operLogging(HistoryType::DELETE, $this->target, $user, ['id' => $id], $user->g_mid);
                return $this->response($res ? 1 : 990, ['id' => $id]);        
            }
        }
        else
            return $this->response(951);
    }
    
    /**
     * 패스워드 변경
     */
    public function passwordChange(Request $request, $id)
    {
        if(Ablilty::isMyGmid($request, $id) || Ablilty::isOperator($request))
        {
            $is_me = Ablilty::isMyGmid($request, $id) ? true : false;
            return $this->_passwordChange($this->gmids->where('id', $id), $request, $is_me);
        }
        else
            return $this->response(951);
    }
}
