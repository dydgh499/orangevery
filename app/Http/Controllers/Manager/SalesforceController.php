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
        if(isSalesforce($request) && $request->sales_parent_structure)
        {
            [$child_ids, $count] = $this->parentStructureSelect($request);
            $data = [
                'total'     => $count,
                'content'   => $this->salesforces->whereIn('id', $child_ids)->get(['created_at', 'is_delete']),
            ];
            $chart = getDefaultUsageChartFormat($data);
            return $this->response(0, $chart);
        }
        else
        {
            $query = $this->commonSelect($request, true);
            $data = $this->getIndexData($request, $query);
            $chart = getDefaultUsageChartFormat($data);
            return $this->response(0, $chart);    
        }
    }

    protected function getUnderSalesforces($request, $offset, $is_select_level)
    {
        $search = $request->input('search', '');
        $getUnderSalesforces = function($is_select_level, $is_dest_level, $parent_ids, $search) {
            $query = $this->salesforces
                ->whereIn('parent_id', $parent_ids)
                ->where('is_delete', false);
            if($is_select_level === false && $search)
            {
                $query = $query->where(function($query) use ($search) {
                    return $query->where('sales_name', 'like', "%$search%")
                        ->orWhere('user_name', 'like', "%$search%");
                });
            }
            else if($is_select_level && $is_dest_level && $search)
            {
                $query = $query->where(function($query) use ($search) {
                    return $query->where('sales_name', 'like', "%$search%")
                        ->orWhere('user_name', 'like', "%$search%");
                });
            }
            return $query->pluck('id')->all();
        };

        $total_ids = [$request->user()->id];
        $parent_ids = $total_ids;
        
        for ($i=0; $i < $offset; $i++) 
        {
            $is_dest_level = $i+1 === $offset;
            $ids = $getUnderSalesforces($is_select_level, $is_dest_level, $parent_ids, $search);

            if($is_select_level && $is_dest_level)
                return $ids;

            if(count($ids))
            {
                $total_ids = array_merge($total_ids, $ids);
                $parent_ids = $ids;    
            }
            else
                return $total_ids;
        }

        return $total_ids;
    }

    protected function parentStructureSelect($request)
    {
        if($request->user()->level === (int)$request->level)
        {
            $search = $request->input('search', '');
            if($search)
            {
                if((strpos($request->user()->sales_name, $search) !== false || strpos($request->user()->user_name, $search) !== false))
                    return [[$request->user()->id], 1];
                else
                    return [[], 0];
            }
            else
                return [[$request->user()->id], 1];
        }

        $my_idx = globalLevelByIndex($request->user()->level);
        if($request->level)
        {
            $dest_idx = globalLevelByIndex($request->level);
            $offset = $my_idx - $dest_idx;
            $child_ids = $this->getUnderSalesforces($request, $offset, true);
        }
        else
            $child_ids = $this->getUnderSalesforces($request, $my_idx, false);

        $page      = $request->input('page');
        $page_size = $request->input('page_size');
        $sp = ($page - 1) * $page_size;
        $count = count($child_ids);
        if($sp <$count)
            $child_ids = array_slice($child_ids, $sp, $page_size);
        else
            return [[], 0];
        return [$child_ids, $count];
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


        $sales_ids = $this->underSalesFilter($request);
        if(count($sales_ids))
            $query = $query->whereIn('salesforces.id', $sales_ids);
        if($request->level)
            $query = $query->where('salesforces.level', $request->level);    

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
        // 영업점이면서, 종속구조사용
        if(isSalesforce($request) && $request->sales_parent_structure)
        {
            [$child_ids, $count] = $this->parentStructureSelect($request);
            $data = [
                'page'      => $request->page, 
                'page_size' => $request->page_size,
                'total'     => $count,
                'content'   => $this->salesforces
                    ->whereIn('id', $child_ids)
                    ->with(['underAutoSettings'])
                    ->get(),
            ];
            return $this->response(0, $data);
        }
        else
        {
            
            $query = $this->commonSelect($request);
            $query->with(['underAutoSettings']);
    
            $data = $this->getIndexData($request, $query);
            return $this->response(0, $data);    
        }
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
            return $this->extendResponse(1999, "추가할 수 없는 등급입니다.");

        $validated = $request->validate(['user_pw'=>'required']);

        if($this->isExistMutual($this->salesforces, $request->user()->brand_id, 'sales_name', $request->sales_name))
            return $this->extendResponse(1001, __("validation.already_exsit", ['attribute'=>'상호']));
        else
        {
            if($this->isExistUserName($request->user()->brand_id, $request->user_name))
                return $this->extendResponse(1001, __("validation.already_exsit", ['attribute'=>'아이디']));
            else
            {
                $user = $request->data();
                $user = $this->saveImages($request, $user, $this->imgs);
                $user['user_pw'] = Hash::make($request->input('user_pw'));
                $res = $this->salesforces->create($user);

                operLogging(HistoryType::CREATE, $this->target, [], $user, $user['sales_name']);
                return $this->response($res ? 1 : 990, ['id'=>$res->id]);
            }
        }
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
        $data = $this->salesforces->where('id', $id)
            ->with(['underAutoSettings'])
            ->first();
        if($data)
        {
            if($request->user()->tokenCan($data->level) === false)
                return $this->response(951);
            else
                return $this->response(0, $data);
        }
        else
            return $this->response(1000);
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
        $data = $request->data();
        $data = $this->saveImages($request, $data, $this->imgs);
        // 상호 검사
        if($this->isExistMutual($this->salesforces->where('id', '!=', $id), $request->user()->brand_id, 'sales_name', $data['sales_name']))
            return $this->extendResponse(1001, __("validation.already_exsit", ['attribute'=>'상호']));
        else
        {
            $query = $this->salesforces->where('id', $id);
            $user = $query->first();
            // 아이디 중복 검사
            if($user->user_name !== $request->user_name && $this->isExistUserName($request->user()->brand_id, $data['user_name']))
                return $this->extendResponse(1001, __("validation.already_exsit", ['attribute'=>'아이디']));
            else
            {
                $res = $query->update($data);
                operLogging(HistoryType::UPDATE, $this->target, $user, $data, $data['sales_name']);
                return $this->response($res ? 1 : 990, ['id'=>$id]);    
            }
        }
    }

    /**
     * 단일삭제
     *
     * @urlParam id integer required 유저 PK
     */
    public function destroy(Request $request, int $id)
    {
        if($this->authCheck($request->user(), $id, 15))
        {
            $res = $this->delete($this->salesforces->where('id', $id));
            $data = $this->salesforces->where('id', $id)->first();

            operLogging(HistoryType::DELETE, $this->target, $data, ['id' => $id], $data->sales_name);
            return $this->response($res ? 1 : 990, ['id'=>$id]);
        }
        else
            return $this->response(951);
    }

    private function getRecursionChilds($data, $sales)
    {
        if($sales)
        {
            for ($i=0; $i <count($sales->childs); $i++) 
            {
                $data = $this->getRecursionChilds($data, $sales->childs[$i]);
            }

            $data["level_".$sales->level][] = $sales;
            $sales->makeHidden(['childs']);
            return $data;    
        }
        else
            return $data;
    }

    private function getParents($request)
    {
        $parents = [];

        $parent_id = $request->user()->parent_id;
        if($parent_id)
        {
            $idx = globalLevelByIndex($request->user()->level);
            for ($i=$idx; $i < 5; $i++)
            {
                $parent = $this->salesforces->where('id', $parent_id)->first(['id', 'parent_id', 'sales_fee', 'level', 'sales_name', 'is_able_under_modify', 'mcht_batch_fee']);
                if($parent)
                    $parents[] = $parent;

                if($parent->parent_id === null)
                    return $parents;
                else
                    $parent_id = $parent->parent_id;
            }
            return $parents;
        }
        else
            return $parents;
    }

    public function classification(Request $request)
    {
        if($request->sales_parent_structure && isSalesforce($request))
        {
            $data = [
                'level_13' => [], 'level_15' => [],
                'level_17' => [], 'level_20' => [], 
                'level_25' => [], 'level_30' => [],
            ];
            // parent
            $parents = $this->getParents($request);
            foreach($parents as $parent)
            {
                $data["level_".$parent->level][] = $parent;
            }
            // child, self
            $sales = $this->salesforces->where('id', $request->user()->id)->with(['childs'])->first();
            $data = $this->getRecursionChilds($data, $sales);
        }
        else
        {
            $data = [];
            if(isMerchandise($request) == false)
            {
                [$levels, $sales_keys] = $this->getViewableSalesInfos($request);
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
        }
        return $this->response(0, $data);
    }

    public function feeApplyHistories(Request $request)
    {
        if(isOperator($request))
        {
            $histories = SfFeeApplyHistory::where('brand_id', $request->user()->brand_id)
            ->where('is_delete', false)
            ->get();
        }
        else
            $histories = [];
        return $this->response(0, $histories);
    }

    public function passwordChange(Request $request, $id)
    {
        $validated = $request->validate(['user_pw'=>'required']);
        $res = $this->salesforces
            ->where('id', $id)
            ->update(['user_pw' => Hash::make($request->user_pw)]);
        return $this->response($res ? 1 : 990);        
    }

    public function mchtBatchFee(Request $request, $id)
    {
        $validated = $request->validate(['mcht_batch_fee'=>'required']);
        $res = $this->salesforces
            ->where('id', $id)
            ->update(['mcht_batch_fee' => $request->mcht_batch_fee/100]);
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
