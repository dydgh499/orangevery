<?php

namespace App\Http\Controllers\Manager;

use App\Models\Salesforce;
use App\Models\Merchandise;
use App\Models\Log\SfFeeApplyHistory;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Traits\StoresTrait;

use App\Http\Requests\Manager\BulkRegister\BulkSalesforceRequest;
use App\Http\Requests\Manager\SalesforceRequest;
use App\Http\Requests\Manager\IndexRequest;
use Illuminate\Support\Facades\Hash;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Enums\HistoryType;

class SalesforceController extends Controller
{
    use ManagerTrait, ExtendResponseTrait, StoresTrait;
    protected $salesforces, $target;

    public function __construct(Salesforce $salesforces)
    {
        $this->salesforces = $salesforces;
        $this->target = '영업점';
        $this->imgs = [
            'params'    => [
                'contract_file', 'id_file', 'passbook_file', 'bsin_lic_file', 'profile_file',
            ],
            'cols'  => [
                'contract_img', 'id_img', 'passbook_img', 'bsin_lic_img', 'profile_img',
            ],
            'folders'   => [
                'contracts', 'ids', 'passbooks', 'bsin_lic', 'profile',
            ],
            'sizes'     => [
                500, 500, 500, 500, 120,
            ],
        ];
    }

    public function chart(Request $request)
    {
        $request->merge([
            'page' => 1,
            'page_size' => 99999999,
        ]);
        $query = $this->commonSelect($request, true);
        $data = $this->getIndexData($request, $query);
        $chart = getDefaultUsageChartFormat($data);
        return $this->response(0, $chart);
    }

    private function commonSelect($request, $is_all=false)
    {
        $search = $request->input('search', '');
        $query = $this->salesforces
            ->where('brand_id', $request->user()->brand_id)
            ->where('sales_name', 'like', "%$search%");

        if($is_all == false)
            $query = $query->where('is_delete', false);            

        if(isSalesforce($request))
        {
            if($request->user()->level == $request->level)
                $query = $query->where('id', $request->user()->id);
            else
            {
                if($request->has('level'))
                {
                    $rq_idx = globalLevelByIndex($request->level);
                    $s_keys = ['sales'.$rq_idx.'_id'];
                }
                else
                {
                    $levels  = $this->getUnderSalesLevels($request);
                    $s_keys = $this->getUnderSalesKeys($levels);
                    logging(['levels'=>$levels, 'keys'=>$s_keys]);
                }
                $sales = $this->getUnderSalesIds($request, $s_keys);
                $sales_ids = $sales->flatMap(function ($sale) use($s_keys) {
                    $keys = [];
                    foreach($s_keys as $s_key)
                    {
                        $keys[] = $sale[$s_key];
                    }
                    return $keys;
                })->unique();
                // 하위가 1000명이 넘으면 ..?
                if(count($sales_ids))
                    $query = $query->whereIn('id', $sales_ids);
            }
        }

        if($request->has('level') && $request->level >= 0)
            $query = $query->where('level', $request->level);
        return $query;
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
        $query = $this->commonSelect($request);
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
    public function store(SalesforceRequest $request)
    {
        if($request->user()->tokenCan(15))
        {
            $validated  = $request->validate(['user_pw'=>'required']);
            $user = $this->salesforces
                ->where('brand_id', $request->user()->brand_id)
                ->where('is_delete', false)
                ->where(function ($query) use ($request) {
                    return $query->where('user_name', $request->user_name)
                        ->orWhere('sales_name', $request->sales_name);
                })
                ->first();
            if(!$user)
            {
                $user = $request->data();
                $user = $this->saveImages($request, $user, $this->imgs);
                $user['user_pw'] = Hash::make($request->input('user_pw'));
                $res = $this->salesforces->create($user);

                operLogging(HistoryType::CREATE, $this->target, $user, $user['sales_name']);
                return $this->response($res ? 1 : 990, ['id'=>$res->id]);
            }
            else
                return $this->extendResponse(1001, __("validation.already_exsit", ['attribute'=>'아이디 또는 상호']));
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
    public function update(SalesforceRequest $request, $id)
    {
        if($this->authCheck($request->user(), $id, 15))
        {
            $data = $request->data();
            $data = $this->saveImages($request, $data, $this->imgs);
            $res = $this->salesforces->where('id', $id)->update($data);

            operLogging(HistoryType::UPDATE, $this->target, $data, $data['sales_name']);
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

            $data = $this->salesforces->where('id', $id)->first(['sales_name']);
            operLogging(HistoryType::DELETE, $this->target, ['id' => $id], $data->sales_name);
            return $this->response($res);
        }
        else
            return $this->response(951);
    }


    private function salesClassFilter($request, $grouped, $levels)
    {
        $my_level = $request->user()->level;
        $my_id = $request->user()->id;

        $sales_keys = $this->getUnderSalesKeys($levels);
        $mchts = $this->getUnderSalesIds($request, $sales_keys);

        for($i=0; $i <count($sales_keys)-1; $i++) 
        {
            $key = $levels[$i];
            if(isset($grouped[$key]))
            {
                $ids = $mchts->pluck($sales_keys[$i])->values()->toArray();
                $grouped[$levels[$i]] = $grouped[$levels[$i]]->filter(function($sales) use($ids){
                    return array_search($sales->id, $ids) !== false;
                })->values();
            }
        }
        $grouped[$my_level] = $grouped[$my_level]->filter(function($sales) use($my_id) {
                return $sales->id == $my_id;
            })->values();
        return $grouped;
    }

    public function getUnderSalesIds($request, $sales_keys)
    {
        $idx = globalLevelByIndex($request->user()->level);
        return Merchandise::where('brand_id', $request->user()->brand_id)
            ->where('sales'.$idx.'_id', $request->user()->id)
            ->where('is_delete', false)
            ->get($sales_keys);
    }

    public function getUnderSalesLevels($request)
    {
        $levels  = [];
        $_levels = [13,15,17,20,25,30];
        for ($i=0; $i<count($_levels); $i++)
        {
            if($request->user()->level >= $_levels[$i])
                array_push($levels, $_levels[$i]);
        }
        return $levels;
    }
    public function getUnderSalesKeys($levels)
    {
        $sales_keys = [];
        for($i=0; $i <count($levels); $i++) 
        {
            $idx = globalLevelByIndex($levels[$i]);
            array_push($sales_keys, 'sales'.$idx.'_id');
        }
        return $sales_keys;
    }
    public function classification(Request $request)
    {
        $data = [];
        if(isMerchandise($request) == false)
        {
            $levels  = $this->getUnderSalesLevels($request);
            $grouped = $this->salesforces
                ->where('brand_id', $request->user()->brand_id)
                ->where('is_delete', false)
                ->get(['id', 'sales_name', 'level'])
                ->groupBy('level');
                
            if(isSalesforce($request))
                $grouped = $this->salesClassFilter($request, $grouped, $levels);   
        }

        for($i=0; $i<count($levels); $i++)
        {
            $level = $levels[$i];
            $data["level_$level"] = isset($grouped[$level]) ? $grouped[$level] : [];
        }
        return $this->response(0, $data);
    }

    public function feeApplyHistories(Request $request)
    {
        $histories = SfFeeApplyHistory::where('brand_id', $request->user()->brand_id)
                ->where('is_delete', false)
                ->get();
        return $this->response(0, $histories);
    }

    public function passwordChange(Request $request)
    {
        $validated = $request->validate(['id'=>'required|integer', 'user_pw'=>'required']);
        $res = $this->salesforces
            ->where('id', $request->id)
            ->update(['user_pw' => Hash::make($request->user_pw)]);
        return $this->response($res ? 1 : 990);        
    }

    public function bulkRegister(BulkSalesforceRequest $request)
    {
        $current = date('Y-m-d H:i:s');
        $brand_id = $request->user()->brand_id;

        $datas = $request->data();
        $user_names = $datas->pluck('user_name')->values()->toArray();
        $exist_sales = $this->salesforces
                ->where('brand_id', $brand_id)
                ->where('is_delete', false)
                ->whereIn('user_name', $user_names)
                ->pluck('user_name')->toArray();

        if(count($exist_sales))
            return $this->extendResponse(1000, join(',', $exist_sales).'는 이미 존재하는 아이디 입니다.');
        else
        {
            $salesforces = $datas->map(function ($data) use($current, $brand_id) {
                $data['user_pw'] = Hash::make($data['user_pw']);
                $data['brand_id'] = $brand_id;
                $data['created_at'] = $current;
                $data['updated_at'] = $current;
                return $data;
            })->toArray();
            $res = $this->manyInsert($this->salesforces, $salesforces);
            return $this->response($res ? 1 : 990);
        }
    }
}
