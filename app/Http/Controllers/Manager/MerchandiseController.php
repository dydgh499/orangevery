<?php

namespace App\Http\Controllers\Manager;

use App\Models\Merchandise;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Requests\Manager\MerchandiseRequest;
use App\Http\Requests\Manager\IndexRequest;
use Illuminate\Support\Facades\Hash;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
/**
 * @group Merchandise API
 *
 * 가맹점 관리 메뉴에서 사용될 API 입니다. 가맹점 이상권한이 요구됩니다.
 */
class MerchandiseController extends Controller
{
    use ManagerTrait, ExtendResponseTrait;
    protected $merchandises;
    protected $imgs;
    public function __construct(Merchandise $merchandises)
    {
        $this->merchandises = $merchandises;
        $this->imgs = [
            'params'    => [
                'contract_file', 'id_file', 'passbook_file', 'bsin_lic_file',
            ],
            'cols'  => [
                'contract_img', 'id_img', 'passbook_img', 'bsin_lic_img',
            ],
            'folders'   => [
                'contracts', 'ids', 'passbooks', 'bsin_lic'
            ],
            'sizes'     => [
                500, 500, 500, 500
            ],
        ];
    }

    /**
     * 목록출력
     *
     * 가맹점 이상 가능
     *
     * @queryParam search string 검색어(유저 ID)
     */
    public function index(IndexRequest $request)
    {
        $cols = ['merchandises.*'];
        $search = $request->input('search', '');
        $query = $this->merchandises->where('merchandises.user_name', 'like', "%$search%");

        array_push($cols, 'brands.name as brand_name');
        $query = $query->join('brands','merchandises.brand_id', 'brands.id');

        if(isMerchandise($request))
            $query = $query->where('merchandises.id', $request->user()->id);
        $query = $query->with(['sales0', 'sales1', 'sales2', 'sales3', 'sales4', 'sales5']);

        $data = $this->getIndexData($request, $query, 'merchandises.ID', $cols, 'merchandises.created_at');
        return $this->response(0, $data);
    }

    /**
     * 추가
     *
     * 대리점 이상 가능
     *
     * @bodyParam user_pw string 유저 패스워드
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(MerchandiseRequest $request)
    {
        $validated  = $request->validate(['user_pw'=>'required']);
        if($request->user()->tokenCan(15))
        {
            $user = $this->merchandises
                ->where('brand_id', $request->user()->brand_id)
                ->where('user_name', $request->user_name)
                ->where('mcht_name', $request->mcht_name)->first();
            if(!$user)
            {
                $user = $request->data();
                $user = $this->saveImages($request, $user, $this->imgs);
                $user['user_pw'] = Hash::make($request->input('user_pw'));
                $user['location'] = $this->merchandises->setLocation(0, 0);
                $res = $this->merchandises->create($user);
                return $this->response($res ? 1 : 990);
            }
            else
                return $this->extendResponse(1001, __("validation.already_exsit", ['attribute'=>'아이디']));
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
            $data = $this->merchandises->where('id', $id)->first();
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
    public function update(MerchandiseRequest $request, $id)
    {
        if($this->authCheck($request->user(), $id, 15))
        {
            $query = $this->merchandises->where('id', $id);
            $data = $request->data();
            $data = $this->saveImages($request, $data, $this->imgs);
            $res = $query->update($data);
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
            $res = $this->delete($this->merchandises->where('id', $id));
            return $this->response($res);
        }
        else
            return $this->response(951);
    }
}
