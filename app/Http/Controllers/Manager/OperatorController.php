<?php

namespace App\Http\Controllers\Manager;

use App\Models\Operator;
use App\Http\Traits\StoresTrait;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Requests\Manager\OperatorReqeust;
use App\Http\Requests\Manager\IndexRequest;
use Illuminate\Support\Facades\Hash;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * @group Operator API
 *
 * 오퍼레이터 관리 메뉴에서 사용될 API 입니다. 본사 이상권한이 요구됩니다.
 */
class OperatorController extends Controller
{
    use ManagerTrait, ExtendResponseTrait, StoresTrait;
    protected $operators;

    public function __construct(Operator $operators)
    {
        $this->operators = $operators;
        $this->imgs = [
            'params'    => [
                 'profile_file',
            ],
            'cols'  => [
                'profile_img',
            ],
            'folders'   => [
                'profile',
            ],
            'sizes'     => [
               360
            ],
        ];
    }

    /**
     * 목록출력
     *
     * 가맹점 이상 가능
     */
    public function index(IndexRequest $request)
    {
        $search = $request->search;
        $query = $this->operators
            ->where('brand_id', $request->user()->brand_id)
            ->where('is_delete', false)
            ->where(function ($query) use ($search) {
                return $query->where('user_name', 'like', "%$search%")
                    ->orWhere('nick_name', 'like', "%$search%");
            });
        $data = $this->getIndexData($request, $query);
        return $this->response(0, $data);
    }

    /**
     * 추가
     *
     * 대리점 이상 가능
     *
     */
    public function store(OperatorReqeust $request)
    {
        $validated = $request->validate(['user_pw'=>'required']);
        $result = $this->isExistUserName($request->user()->brand_id, $request->user_name);
        if($result === false)
        {
            $user = $request->data();
            $user = $this->saveImages($request, $user, $this->imgs);
            $user['user_pw'] = Hash::make($request->input('user_pw'));
            $res = $this->operators->create($user);
            return $this->response($res ? 1 : 990, ['id'=>$res->id]);    
        }
        else
            return $this->extendResponse(1001, __("validation.already_exsit", ['attribute'=>'아이디']));
    }

    /**
     * 단일조회
     *
     * 가맹점 이상 가능
     *
     * @urlParam id integer required 유저 PK
     */
    public function show(Request $request, $id)
    {
        $data = $this->operators->where('id', $id)->first();
        return $data ? $this->response(0, $data) : $this->response(1000);
    }

    /**
     * 업데이트
     *
     * 가맹점 이상 가능
     *
     * @urlParam id integer required 유저 PK
     */
    public function update(OperatorReqeust $request, $id)
    {
        $user = $request->data();
        $user = $this->saveImages($request, $user, $this->imgs);
        $res = $this->operators->where('id', $id)->update($user);
        return $this->response($res ? 1 : 990);
    }

    /**
     * 단일삭제
     *
     * @urlParam id integer required 유저 PK
     */
    public function destroy(Request $request, $id)
    {
        $res = $this->delete($this->operators->where('id', $id));
        return $this->response($res ? 1 : 990, ['id'=>$id]);
    }

    public function passwordChange(Request $request)
    {
        $validated = $request->validate(['id'=>'required|integer', 'user_pw'=>'required']);
        $res = $this->operators
            ->where('id', $request->id)
            ->update(['user_pw' => Hash::make($request->user_pw)]);
        return $this->response($res ? 1 : 990);
    }
}
