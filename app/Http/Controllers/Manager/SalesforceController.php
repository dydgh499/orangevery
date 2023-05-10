<?php

namespace App\Http\Controllers\Manager;

use App\Models\Salesforce;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Traits\StoresTrait;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SalesforceController extends Controller
{
    use ManagerTrait, ExtendResponseTrait, StoresTrait;
    protected $salesforces;

    public function __construct(Salesforce $salesforces)
    {
        $this->salesforces = $salesforces;
        $this->imgs = [
            'params'    => ['profile_img'],
            'folders'   => ['profiles'],
            'sizes'     => [120],
        ];
    }

    /**
     * 목록출력
     *
     * 가맹점 이상 가능
     *
     * @queryParam search string 검색어(유저 ID)
     */
    public function index(IndexForm $request)
    {
        $search = $request->input('search', '');
        $query = $this->salesforces
            ->where('brand_id', $request->user()->brand_id)
            ->where('user_name', 'like', "%$search%");

        if($this->isMerchandise($request))
            $query = $query->where('id', $request->user()->id);

        $data = $this->getIndexData($request, $query);
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
    public function store(MerchandiseForm $request)
    {
        if($request->user()->tokenCan(15))
        {
            $validated  = $request->validate(['user_pw'=>'required']);
            $user = $this->salesforces
                ->where('brand_id', $request->user()->brand_id)
                ->where('user_name', $request->user_name)->first();
            if(!$user)
            {
                $user = $request->data();
                $user = $this->saveImages($request, $user, $this->imgs);
                $user['user_pw'] = Hash::make($request->input('user_pw'));
                $res = $this->salesforces->create($user);
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
            $data = $this->salesforces->where('id', $id)->first();
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
    public function update(MerchandiseForm $request, $id)
    {
        if($this->authCheck($request->user(), $id, 15))
        {
            $data = $request->data();
            $data = $this->saveImages($request, $data, $this->imgs);
            $res = $this->salesforces->where('id', $id)->update($data);
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
            $res = $this->delete($this->salesforces->where('id', $id));
            return $this->response($res);
        }
        else
            return $this->response(951);
    }

    /**
     * 패스워드 변경
     *
     * 가맹점 이상 가능
     *
     * @urlParam id integer required 유저 PK
     * @bodyParam new_user_pw integer required 새로운 패스워드 번호
     * @return \Illuminate\Http\JsonResponse
     */
    public function setPassword(Request $request, $id)
    {
        return $this->__setPassword($this->salesforces, $request, $id);
    }

    private function __storesValidate($jsons, $mchts)
    {
        $result = [];
        $result['fail'] = $this->existenceMerchandiseValidate($mchts, $jsons);
        if(count($result['fail']) == 0)
            $result['code'] = 0;
        else
        {
            $result['code'] = 1000;
            $result['msg'] = __('validation.not_found', ['attribute'=>__('validation.attributes.partner_name')]);

        }
        return $result;
    }

    /**
     * 대량등록 검증
     *
     * 마스터 이상 가능
     */
    public function storesValidate(MerchandiseStoresForm $request)
    {
        $mchts = Merchandise::where('brand_id', $request->user()->brand_id)->get(['id', 'user_name'])->toArray();
        $result = $this->__storesValidate($request->all(), $mchts);
        if($result['code'] == 0)
            return $this->response($result['code']);
        else
            return $this->extendResponse($result['code'], $result['msg'], $result['fail']);
    }

    /**
     * 대량등록
     *
     * 마스터 이상 가능
     */
    public function stores(MerchandiseStoresForm $request)
    {
        $brand_id = $request->user()->brand_id;
        $jsons  = $request->all();

        $mchts = Merchandise::where('brand_id', $brand_id)->get(['id', 'user_name'])->toArray();
        $result = $this->__storesValidate($jsons, $mchts);
        if($result['code'] == 0)
        {
            $salesforces = [];
            $mytime     = Carbon::now();
            $cur_dttm   = $mytime->toDateTimeString();

            for($i=0; $i <count($jsons); $i++)
            {
                $merchandise = $jsons[$i];
                $merchandise['brand_id'] = $brand_id;
                $merchandise['group_id'] = 0;
                $merchandise['profile_img'] = '';
                $merchandise['user_pw']     = Hash::make($merchandise['user_pw']);
                $merchandise['birth_date']  = isset($jsons[$i]['birth_date']) ? $jsons[$i]['birth_date'] : '1900-01-01';
                $merchandise['created_at']  = $cur_dttm;
                $merchandise['updated_at']  = $cur_dttm;
                array_push($salesforces, $merchandise);

            }
            $res = $this->manyInsert($this->salesforces, $salesforces);
            return $this->response($res ? 1 : 990);
        }
        else
            return $this->extendResponse($result['code'], $result['msg'], $result['fail']);
    }
}
