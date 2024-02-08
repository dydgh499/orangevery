<?php

namespace App\Http\Controllers\Manager;

use App\Models\Salesforce;
use App\Models\Merchandise;
use App\Models\Log\SfFeeApplyHistory;
use App\Http\Traits\StoresTrait;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Traits\Salesforce\UnderSalesTrait;

use App\Http\Requests\Manager\BulkRegister\BulkSalesforceRequest;
use App\Http\Requests\Manager\SalesforceRequest;
use App\Http\Requests\Manager\IndexRequest;
use Illuminate\Support\Facades\Hash;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Enums\HistoryType;

/**
 * @group Salesforce API
 *
 * 영업자 API 입니다.
 */
class SalesforceController extends Controller
{
    use ManagerTrait, ExtendResponseTrait, StoresTrait, UnderSalesTrait;
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
    
    /**
     * 차트 데이터 출력
     *
     * 가맹점 이상 가능
     */
    public function chart(Request $request)
    {
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
            ->where(function ($query) use ($search) {
                return $query->where('sales_name', 'like', "%$search%")
                ->orWhere('user_name', 'like', "%$search%");
        });

        if($is_all == false)
            $query = $query->where('is_delete', false);

        if(isSalesforce($request))
        {
            $sales_ids = $this->underSalesFilter($request);
            // 하위가 1000명이 넘으면 ..?
            $query = $query->whereIn('salesforces.id', $sales_ids);
        }
        else
        {
            if($request->input('level', false))
                $query = $query->where('level', $request->level);
        }
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
        if($request->use_sales_auto_setting)
            $query->with(['underAutoSettings']);

        $data = $this->getIndexData($request, $query);
        return $this->response(0, $data);
    }

    /**
     * 추가
     *
     * 대리점 이상 가능
     *
     * @bodyParam user_pw string 유저 패스워드
     */
    public function store(SalesforceRequest $request)
    {
        if(isSalesforce($request) &&  $request->level >= $request->user()->level)
            return $this->extendResponse(951, "추가할 수 없는 등급입니다.");

        $validated    = $request->validate(['user_pw'=>'required']);
        $exist_mutual = $this->isExistMutual($this->salesforces, $request->user()->brand_id, 'sales_name', $request->sales_name);
        if(!$exist_mutual)
        {
            $result = $this->isExistUserName($request->user()->brand_id, $request->user_name);
            if($result === false)
            {
                $user = $request->data();
                $user = $this->saveImages($request, $user, $this->imgs);
                $user['user_pw'] = Hash::make($request->input('user_pw'));
                $res = $this->salesforces->create($user);

                operLogging(HistoryType::CREATE, $this->target, $user, $user['sales_name']);
                return $this->response($res ? 1 : 990, ['id'=>$res->id]);
            }
            else    
                return $this->extendResponse(1001, __("validation.already_exsit", ['attribute'=>'아이디']));
        }
        else
            return $this->extendResponse(1001, __("validation.already_exsit", ['attribute'=>'상호']));
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
        if($this->authCheck($request->user(), $id, 15))
        {
            $data = $this->salesforces->where('id', $id)
                ->with(['underAutoSettings'])
                ->first();
            return $data ? $this->response(0, $data) : $this->response(1000);
        }
        else
            return $this->response(951);
    }

    /**
     * 업데이트
     *
     * 영업점 이상 가능
     *
     * @urlParam id integer required 유저 PK
     */
    public function update(SalesforceRequest $request, $id)
    {
        if($this->authCheck($request->user(), $id, 15))
        {
            $data = $request->data();
            $data = $this->saveImages($request, $data, $this->imgs);
            $res = $this->salesforces->where('id', $id)->update($data);

            operLogging(HistoryType::UPDATE, $this->target, $data, $data['sales_name']);
            return $this->response($res ? 1 : 990, ['id'=>$id]);
        }
        else
            return $this->response(951);
    }

    /**
     * 단일삭제
     *
     * @urlParam id integer required 유저 PK
     */
    public function destroy(Request $request, $id)
    {
        if($this->authCheck($request->user(), $id, 15))
        {
            $res = $this->delete($this->salesforces->where('id', $id));
            $data = $this->salesforces->where('id', $id)->first(['sales_name']);

            operLogging(HistoryType::DELETE, $this->target, ['id' => $id], $data->sales_name);
            return $this->response($res ? 1 : 990, ['id'=>$id]);
        }
        else
            return $this->response(951);
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
                ->with(['underAutoSettings'])
                ->get(['id', 'sales_name', 'level'])
                ->groupBy('level');
                
            if(isSalesforce($request))
                $grouped = $this->salesClassFilter($request, $grouped, $levels);   

            for($i=0; $i<count($levels); $i++)
            {
                $level = $levels[$i];
                $data["level_$level"] = isset($grouped[$level]) ? $grouped[$level] : [];
            }
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

        $exist_names = $this->isExistBulkUserName($brand_id, $datas->pluck('user_name')->all());
        $exist_sales = $this->isExistBulkMutual($this->salesforces, $brand_id, 'sales_name', $datas->pluck('sales_name')->all());
        
        if(count($exist_names))
            return $this->extendResponse(1000, join(',', $exist_names).'는 이미 존재하는 아이디 입니다.');
        else if(count($exist_sales))
            return $this->extendResponse(1000, join(',', $exist_sales).'는 이미 존재하는 상호 입니다.');
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
