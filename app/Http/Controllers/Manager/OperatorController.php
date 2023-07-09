<?php

namespace App\Http\Controllers\Manager;

use App\Models\Operator;
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
    use ManagerTrait, ExtendResponseTrait;
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
               120
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
        $query = $this->operators->where('brand_id', $request->user()->brand_id);
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
    public function store(OperatorReqeust $request)
    {
        $validated = $request->validate(['user_pw'=>'required']);
        $user = $request->data();
        $user = $this->saveImages($request, $user, $this->imgs);
        $user['user_pw'] = Hash::make($request->input('user_pw'));
        $res = $this->operators->create($user);
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
        $data = $this->operators->where('id', $id)->first();
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, $id)
    {
        $res = $this->operators->where('id', $id)->update(['is_delete'=>false]);
        return $this->response($res ? 1 : 990);
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
