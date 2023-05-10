<?php

namespace App\Http\Controllers\Manager;

use App\Models\User;
use App\Models\Point;
use App\Models\Merchandise;
use App\Http\Traits\StoresTrait;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Requests\Manager\IndexForm;
use App\Http\Requests\Manager\PointForm;
use App\Http\Requests\Manager\PointStoresForm;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * @group Point API
 *
 * 포인트 발급 관리 메뉴에서 사용될 API 입니다. 조회를 제외하고 마스터 이상권한이 요구됩니다.
 */
class PointController extends Controller
{
    use ManagerTrait, ExtendResponseTrait, StoresTrait;
    protected $points;

    public function __construct(Point $points)
    {
        $this->points   = $points;
        $this->cols     = ['points.*', 'users.user_name', 'merchandises.mcht_name', 'users.user_name'];
    }

    /**
     * 목록출력
     *
     * 가맹점 이상 가능
     *
     * @queryParam is_cancel required 취소(0=미취소, 1=취소)
     * @queryParam search string 검색어(유저 ID, 가맹점명)
     * @queryParam user_id string 유저 고유번호
     */
    public function index(IndexForm $request)
    {
        // Query parameters
        $validated  = $request->validate(['is_cancel'=>'required|numeric']);
        $is_cancel  = $request->input('is_cancel', -1);
        $search     = $request->input('search', '');
        $query      = $this->points->merchandise()->where('users.brand_id', $request->user()->brand_id);

        if($request->has('user_id'))
            $query = $query->where('points.user_id', $request->user_id);

        if($this->isMerchandise($request))
            $query = $query->where('merchandises.mcht_id', $request->user()->id);

        if($is_cancel != -1)
            $query = $query->where('points.is_cancel', $is_cancel);

        $query = $query->where(function ($query) use ($search) {
            $query->where('users.user_name', 'like', "%$search%")
                ->orWhere('merchandises.mcht_name', 'like', "%$search%");
        });
        $data = $this->getIndexData($request, $query, 'points.id', $this->cols, 'points.created_at');
        return $this->response(0, $data);
    }

    /**
     * 추가
     *
     * 가맹점 이상 가능
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(PointForm $request)
    {
        $data = $request->data();
        $res  = $this->points->create($data);
        return $this->response($res ? 1 : 990);
    }

    /**
     * 단일조회
     *
     * 가맹점 이상 가능
     *
     * @urlParam id integer required 포인트 PK
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $data = $this->points->merchandise()->where('points.id', $id)->first($this->cols);
        return $this->response($data ? 0 : 1000, $data);
    }

    /**
     * 업데이트
     *
     * 가맹점 이상 가능
     *
     * @urlParam id integer required 포인트 PK
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(PointForm $request, $id)
    {
        $data = $request->data();
        $result  = $this->points->where('id', $id)->update($data);
        return $this->response($result ? 1 : 990);
    }

    /**
     * 단일삭제
     *
     * 가맹점 이상 가능
     *
     * @urlParam id integer required 포인트 PK
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $result = $this->delete($this->points->where('id', $id));
        return $this->response($result);
    }

    private function __storesValidate($jsons, $users, $mchts)
    {
        $result = [];
        $result['fail'] = $this->existenceValidate($mchts, $jsons, 'mcht_name', 'user_name');
        if(count($result['fail']) == 0)
        {
            $result['fail'] = $this->existenceValidate($users, $jsons, 'user_name', 'user_name');
            if(count($result['fail']) == 0)
                $result['code'] = 0;
            else
            {
                $result['code'] = 1000;
                $result['msg'] = "존재하지않은 유저 아이디가 발견되었어요 😨";
            }
        }
        else
        {
            $result['code'] = 1000;
            $result['msg'] = "존재하지않은 가맹점 아이디가 발견되었어요 😨";

        }
        return $result;
    }

    /**
     * 대량등록 검증
     *
     * 마스터 이상 가능
     */
    public function storesValidate(PointStoresForm $request)
    {
        $brand_id = $request->user()->brand_id;
        $jsons  = $request->all();
        $users  = User::where('brand_id', $brand_id)->get(['id', 'mcht_id', 'user_name'])->toArray();
        $mchts  = Merchandise::where('brand_id', $brand_id)->get(['id', 'user_name'])->toArray();

        $result = $this->__storesValidate($jsons, $users, $mchts);
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
    public function stores(PointStoresForm $request)
    {
        $brand_id = $request->user()->brand_id;
        $jsons  = $request->all();
        $users  = User::where('brand_id', $brand_id)->get(['id', 'mcht_id', 'user_name'])->toArray();
        $mchts  = Merchandise::where('brand_id', $brand_id)->get(['id', 'user_name'])->toArray();

        $result = $this->__storesValidate($jsons, $users, $mchts);
        if($result['code'] == 0)
        {
            $points = [];
            $mytime = Carbon::now();
            $cur_dttm = $mytime->toDateTimeString();

            for($i=0; $i <count($jsons); $i++)
            {
                $is_cancel = isset($jsons[$i]['is_cancel']) ? $jsons[$i]['is_cancel'] : 0;
                $user = $this->getItemByCol($users, $jsons[$i]['user_name'], 'user_name');
                $mcht = $this->getItemByCol($mchts, $jsons[$i]['mcht_name'], 'user_name');
                $save_amount = (int)(($jsons[$i]['purchase_price'] - $jsons[$i]['use_amount']) * ($jsons[$i]['point_rate']/100));
                $point = [
                    'user_id' => $user['id'],
                    'mcht_id' => $mcht['id'],
                    'purchase_price'=> $jsons[$i]['purchase_price'],
                    'use_amount'    => $jsons[$i]['use_amount'],
                    'save_amount'   => $save_amount,
                    'point_rate'    => $jsons[$i]['point_rate'],
                    'is_cancel'     => $is_cancel,
                    'created_at'    => $cur_dttm,
                    'updated_at'    => $cur_dttm,
                ];
                array_push($points, $point);
            }
            $res = $this->manyInsert($this->points, $points);
            return $this->response($res ? 1 : 990);
        }
        else
            return $this->extendResponse($result['code'], $result['msg'], $result['fail']);
    }
}
