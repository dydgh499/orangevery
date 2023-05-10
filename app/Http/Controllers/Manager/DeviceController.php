<?php

namespace App\Http\Controllers\Manager;

use App\Models\User;
use App\Models\Device;
use App\Models\Merchandise;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\StoresTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Requests\Manager\DeviceForm;
use App\Http\Requests\Manager\DeviceStoresForm;
use App\Http\Requests\Manager\IndexForm;
use Carbon\Carbon;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

/**
 * @group Device API
 *
 * 장비 관리 메뉴에서 사용될 API 입니다. 조회를 제외하고 가맹점 이상권한이 요구됩니다.
 */
class DeviceController extends Controller
{
    use ManagerTrait, ExtendResponseTrait, StoresTrait;
    protected $devices;

    public function __construct(Device $devices)
    {
        $this->devices = $devices;
    }

    /**
     * 목록출력
     *
     * 가맹점 이상 가능
     *
     * @queryParam search string 검색어(맥주소)
     */
    public function index(IndexForm $request)
    {
        // Query parameters
        $search = $request->input('search', '');
        $cols = [
            'devices.*', 'merchandises.mcht_name',
            'merchandises.point_flag', 'merchandises.stamp_flag',
            'merchandises.point_rate', 'merchandises.stamp_save_count'
        ];
        $query  = $this->devices->merchandise()->where('merchandises.brand_id', $request->user()->brand_id);
        if($this->isMainBrand($request))
        {
            $cols = array_merge($cols, ['brands.id as brand_id', 'brands.name as brand_name']);
            $query = $request->user()->tokenCan(45) ? $query->where('devices.partner_id' , $request->user()->id) : $query;
        }
        else
            $query = $this->isMerchandise($request) ? $query->where('devices.mcht_id' , $request->user()->id) : $query;

        $query = $query->where(function ($query) use ($search) {
            return $query->where('devices.mac_addr', 'like', "%$search%")
                ->orWhere('merchandises.mcht_name', 'like', "%$search%");
        });
        $data   = $this->getIndexData($request, $query, 'devices.id', $cols, 'devices.created_at');
        return $this->response(0, $data);
    }
    /**
     * 추가
     *
     * 가맹점 이상 가능
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(DeviceForm $request)
    {
        $data = $request->data();
        $res  = $this->devices->create($data);
        return $this->response($res ? 1 : 990);
    }

    /**
     * 단일조회
     *
     * 가맹점 이상 가능
     *
     * @urlParam id integer required 장비 ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $data = $this->devices->where('id', $id)->first();
        return $this->response($data ? 0 : 1000, $data);
    }

    /**
     * 업데이트
     *
     * 가맹점 이상 가능
     *
     * @urlParam id integer required 장비 ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(DeviceForm $request, $id)
    {
        $data = $request->data();
        $res  = $this->devices->where('id', $id)->update($data);
        return $this->response($res ? 1 : 990);
    }

    /**
     * 단일삭제
     *
     * 가맹점 이상 가능
     *
     * @urlParam id integer required 장비 PK
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $result = $this->delete($this->devices->where('id', $id));
        return $this->response($result);
    }

    private function __storesValidate($jsons, $partners, $mchts)
    {
        $result     = [];
        $temp_jsons = [];
        foreach($jsons as $json)
        {
            if(isset($json['partner_name']))
                array_push($temp_jsons, $json);
        }

        $result['fail'] = $this->existenceValidate($partners, $temp_jsons, 'partner_name', 'user_name');
        if(count($result['fail']) == 0)
        {
            $result['fail'] = $this->existenceValidate($mchts, $jsons, 'user_name', 'user_name');
            if(count($result['fail']) == 0)
                $result['code'] = 0;
            else
            {
                $result['code'] = 1000;
                $result['msg'] = __('validation.not_found', ['attribute'=>__('validation.attributes.mcht_name')]);
            }
        }
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
    public function storesValidate(DeviceStoresForm $request)
    {
        $jsons      = $request->all();
        $partners   = User::where('level', 45)->get(['id', 'user_name'])->toArray();
        $mchts      = Merchandise::where('brand_id', $request->user()->brand_id)->get(['id', 'user_name'])->toArray();

        $result = $this->__storesValidate($jsons, $partners, $mchts);
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
    public function stores(DeviceStoresForm $request)
    {
        $jsons      = $request->all();
        $partners   = User::where('level', 45)->get(['id', 'user_name'])->toArray();
        $mchts      = Merchandise::where('brand_id', $request->user()->brand_id)->get(['id', 'user_name'])->toArray();

        $result = $this->__storesValidate($jsons, $partners, $mchts);
        if($result['code'] == 0)
        {
            $devices    = [];
            $mytime     = Carbon::now();
            $cur_dttm   = $mytime->toDateTimeString();

            for($i=0; $i <count($jsons); $i++)
            {
                $partner_id = isset($jsons[$i]['partner_name']) ? $this->getItemByCol($partners, $jsons[$i]['partner_name'], 'user_name')['id'] : 0;
                $mcht = $this->getItemByCol($mchts, $jsons[$i]['user_name'], 'user_name');
                if(count($mcht) == 0)
                    return $this->extendResponse(1000, __('validation.not_found', ['attribute'=>__('validation.attributes.mcht_user_name')]), ['user_name'=>[$i]]);
                else
                {
                    $device = [
                        'partner_id'=> $partner_id,
                        'mcht_id'   => $mcht['id'],
                        'mac_addr'  => $jsons[$i]['mac_addr'],
                        'comment'   => isset($jsons[$i]['comment']) ? $jsons[$i]['comment'] : "",
                        'created_at'    => $cur_dttm,
                        'updated_at'    => $cur_dttm,
                    ];
                    array_push($devices, $device);
                }
            }
            $res = $this->manyInsert($this->devices, $devices);
            return $this->response($res ? 1 : 990);
        }
        else
            return $this->extendResponse($result['code'], $result['msg'], $result['fail']);
    }
}
