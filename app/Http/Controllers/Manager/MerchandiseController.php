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

        $query = $this->merchandises->leftJoin('payment_modules', 'merchandises.id', '=', 'payment_modules.mcht_id');
        $query = globalPGFilter($query, $request, 'payment_modules');
        $query = globalSalesFilter($query, $request, 'merchandises');
        $query = globalAuthFilter($query, $request, 'merchandises');
        $query = $query
            ->where('merchandises.brand_id', $request->user()->brand_id)
            ->where('merchandises.mcht_name', 'like', "%$search%");

        $query = $query->with(['sales0', 'sales1', 'sales2', 'sales3', 'sales4', 'sales5']);
        $query = $query->groupBy('merchandises.id');
        $data = $this->getIndexData($request, $query, 'merchandises.id', $cols, 'merchandises.created_at', true);
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
                $user['sales0_id'] = $request->input('sales0_id', 0);
                $user['sales1_id'] = $request->input('sales1_id', 0);
                $user['sales2_id'] = $request->input('sales2_id', 0);
                $user['sales3_id'] = $request->input('sales3_id', 0);
                $user['sales4_id'] = $request->input('sales4_id', 0);
                $user['sales5_id'] = $request->input('sales5_id', 0);
                $user['hold_fee']  = $request->input('hold_fee', 0)/100;
                $user['trx_fee']    = $request->input('trx_fee', 0)/100;
                $user['sales0_fee'] = $request->input('sales0_fee', 0)/100;
                $user['sales1_fee'] = $request->input('sales1_fee', 0)/100;
                $user['sales2_fee'] = $request->input('sales2_fee', 0)/100;
                $user['sales3_fee'] = $request->input('sales3_fee', 0)/100;
                $user['sales4_fee'] = $request->input('sales4_fee', 0)/100;
                $user['sales5_fee'] = $request->input('sales5_fee', 0)/100;

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
