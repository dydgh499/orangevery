<?php

namespace App\Http\Controllers\Manager;

use App\Models\User;
use App\Models\Merchandise;
use App\Models\Salesforce;

use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Traits\ManagerSubUserTrait;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

/**
 * @group Util API
 *
 * 공통적으로 사용되는 API 입니다.
 */
class UtilController extends Controller
{
    use ManagerTrait, ExtendResponseTrait, ManagerSubUserTrait;
    protected $users;

    public function __construct(User $users, Merchandise $mchts)
    {
        $this->users = $users;
        $this->mchts = $mchts;
    }

    /**
     * 유저 및 가맹점 검색
     *
     * 로그인한 유저 권한 기반으로 하위유저들을 조회합니다.
     *
     * @bodyParam user boolean required user_id 조회여부(1,0) Example: 1
     * @bodyParam mcht boolean required mcht_id 조회여부(1,0), 레벨 10 이상만 가능합니다. Example: 1
     * @bodyParam phone_num string 유저명 Example: 01000000000
     * @bodyParam mcht_name string 가맹점명 Example: 서울도곡렉슬점
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        $data = [];
        $data['users'] = $request->input('user', 0) ? $this->getUserByName($request) : [];
        $data['mchts'] = $request->input('mcht', 0) ? $this->geMchtByName($request) : [];
        return $this->response(0, $data);
    }

    /**
     * 하위 유저 조회
     *
     * 로그인한 유저 권한 기반으로 하위유저들을 조회합니다.
     *
     * @queryParam user boolean required user_id 조회여부(1,0) Example: 1
     * @queryParam mcht boolean mcht_id 조회여부(1,0), 레벨 10 이상만 가능합니다. Example: 1
     * @queryParam user_sort string 조회할 컬럼명 입력 (유저관리 컬럼 모두)
     * @queryParam mcht_sort string 조회할 컬럼명 입력 (가맹점관리 컬럼 모두)
     * @queryParam user_seq string 조회 방식
     * @queryParam mcht_seq string 조회 방식
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSubUsers(Request $request)
    {
        $brand_id = $request->user()->brand_id;
        if($request->level > 40)
            return Salesforce::where('brand_id', $brand_id)->get(['nick_name', 'trx_fee', 'id']);
        else
        {
            $id = $request->user()->id;
            $fees   = Feehistories::where('brand_id', $brand_id)->get()->orderby('create_at');
            $idx    = array_search($id, array_column($fees, 'user_id'));
            if($idx !== false)
            {

            }
            else
            {
                
            }
        }
    }



    /**
     * 하위 유저 조회(트리)
     *
     * 로그인한 유저 권한 기반으로 하위유저들을 트리형태로 볼러옵니다.
     * @queryParam desc string 조회할 컬럼명 입력 Example: 1
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSubTree(Request $request)
    {
        $sel_b = "SELECT id, user_name, group_id, level, brand_id FROM users B WHERE brand_id=1 AND id=".$request->user()->id;
        $sel_a = "SELECT B.id, B.user_name, B.group_id, B.level, B.brand_id FROM users A INNER JOIN users B ON A.group_id = B.id AND A.brand_id = B.brand_id";
        $sql = "WITH TREE AS (
            $sel_b
            UNION ALL
            $sel_a
        )
        SELECT * FROM TREE ORDER BY LEVEL DESC";
        $users = DB::select($sql);
        return $this->response(0, $users);
    }

    /**
     * 이미지 저장
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function imageSave(equest $request)
    {
        # code...
        return '';
    }
}
