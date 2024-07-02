<?php

namespace App\Http\Controllers\Manager;

use App\Models\Salesforce;
use App\Models\Merchandise;
use App\Models\Log\SfFeeApplyHistory;

use App\Http\Controllers\Ablilty\Ablilty;

use App\Http\Traits\StoresTrait;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Traits\Models\EncryptDataTrait;
use App\Http\Traits\Salesforce\UnderSalesTrait;
use App\Http\Controllers\Manager\Salesforce\SalesforceOverlap;
use App\Http\Controllers\Ablilty\AbnormalConnection;

use App\Http\Controllers\Auth\AuthPasswordChange;
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
    use ManagerTrait, ExtendResponseTrait, StoresTrait, UnderSalesTrait, EncryptDataTrait;
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

        $sales_ids = $this->underSalesFilter($request);
        if(count($sales_ids))
            $query = $query->whereIn('salesforces.id', $sales_ids);
        if($request->level)
            $query = $query->where('salesforces.level', $request->level);    
        if($request->is_lock)
            $query = $query->where('salesforces.is_lock', 1);
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
        if($request->sales_parent_structure)
        {
            [$total_count, $content] =SalesforceOverlap::OverlapSearch($request);
            $data = [
                'page'      => $request->page, 
                'page_size' => $request->page_size,
                'total'     => $total_count,
                'content'   => $content
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
        if(Ablilty::isSalesforce($request) &&  $request->level >= $request->user()->level)
            return $this->extendResponse(1999, "추가할 수 없는 등급입니다.");
        if(Ablilty::isEditAbleTime() === false)
            return $this->extendResponse(1500, '지금은 작업할 수 없습니다.');

        $validated = $request->validate(['user_pw'=>'required']);

        if($this->isExistMutual($this->salesforces, $request->user()->brand_id, 'sales_name', $request->sales_name))
            return $this->extendResponse(1001, __("validation.already_exsit", ['attribute'=>'상호']));
        else
        {
            if($this->isExistUserName($request->user()->brand_id, $request->user_name))
                return $this->extendResponse(1001, __("validation.already_exsit", ['attribute'=>'아이디']));
            else
            {
                $current = date('Y-m-d H:i:s');
                $user = $request->data();
                
                [$result, $msg] = AuthPasswordChange::registerValidate($user['user_name'], $request->user_pw);
                if($result === false)
                    return $this->extendResponse(954, $msg, []);
                else
                {
                    $user = $this->saveImages($request, $user, $this->imgs);
                    $user['user_pw'] = Hash::make($request->user_pw.$current);
                    $user['created_at'] = $current;
                    $res = $this->salesforces->create($user);
    
                    operLogging(HistoryType::CREATE, $this->target, [], $user, $user['sales_name']);
                    return $this->response($res ? 1 : 990, ['id'=>$res->id]);    
                }
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
    public function show(Request $request, int $id)
    {
        $data = $this->salesforces->where('id', $id)
            ->with(['underAutoSettings'])
            ->first();
        if($data)
        {
            $sales_cond_1 = (Ablilty::isMySalesforce($request, $id) || $data->level < $request->user()->level);
            if(($sales_cond_1 || Ablilty::isOperator($request)) === false)
            {   // URL 조작 (영업점인데 하위가아닌 다른영업점 조회하려할 시) 자신아래 영업점이 아닌경우?
                AbnormalConnection::tryParameterModulationApproach();
                return $this->response(951);
            }
            if(Ablilty::isBrandCheck($request, $data->brand_id) === false)
                return $this->response(951);
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
    public function update(SalesforceRequest $request, int $id)
    {
        $data = $request->data();
        $data = $this->saveImages($request, $data, $this->imgs);
        // 상호 검사
        if(Ablilty::isEditAbleTime() === false)
            return $this->extendResponse(1500, '지금은 작업할 수 없습니다.');
        if($this->isExistMutual($this->salesforces->where('id', '!=', $id), $request->user()->brand_id, 'sales_name', $data['sales_name']))
            return $this->extendResponse(1001, __("validation.already_exsit", ['attribute'=>'상호']));
        else
        {
            $query = $this->salesforces->where('id', $id);
            $user = $query->first();

            if(Ablilty::isBrandCheck($request, $user->brand_id) === false)
                return $this->response(951);
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
        if(Ablilty::isEditAbleTime() === false)
            return $this->extendResponse(1500, '지금은 작업할 수 없습니다.');
        if($this->authCheck($request->user(), $id, 15))
        {
            $data = $this->salesforces->where('id', $id)->first();
            if(Ablilty::isBrandCheck($request, $data->brand_id) === false)
                return $this->response(951);

            $res = $this->delete($this->salesforces->where('id', $id));
            operLogging(HistoryType::DELETE, $this->target, $data, ['id' => $id], $data->sales_name);
            return $this->response($res ? 1 : 990, ['id'=>$id]);
        }
        else
            return $this->response(951);
    }

    public function classification(Request $request)
    {
        if($request->sales_parent_structure && Ablilty::isSalesforce($request))
            $data = SalesforceOverlap::overlapClassification($request);
        else
        {
            $data = [];
            if(Ablilty::isMerchandise($request) == false)
            {
                [$levels, $sales_keys] = $this->getViewableSalesInfos($request);
                $grouped = $this->salesforces
                    ->where('brand_id', $request->user()->brand_id)
                    ->where('is_delete', false)
                    ->with(['underAutoSettings'])
                    ->get(['id', 'sales_name', 'level', 'settle_tax_type', 'parent_id', 'is_able_under_modify', 'mcht_batch_fee', 'sales_fee'])
                    ->groupBy('level');

                if(Ablilty::isSalesforce($request))
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
        if(Ablilty::isOperator($request))
        {
            $histories = SfFeeApplyHistory::where('brand_id', $request->user()->brand_id)
            ->where('is_delete', false)
            ->get();
        }
        else
            $histories = [];
        return $this->response(0, $histories);
    }

    /**
     * 패스워드 변경
     *
     */
    public function passwordChange(Request $request, int $id)
    {
        if(Ablilty::isMySalesforce($request, $id) || Ablilty::isOperator($request))
            return $this->_passwordChange($this->salesforces->where('id', $id), $request);
        else
            return $this->response(951);
    }

    /**
     * 계정잠금해제
     */
    public function unlockAccount(Request $request, int $id)
    {
        if(Ablilty::isOperator($request))
            return $this->_unlockAccount($this->salesforces->where('id', $id));
        else
            return $this->response(951);
    }

    public function create2FAQRLink(Request $request, int $id)
    {
        if(Ablilty::isMySalesforce($request, $id))
            return $this->_create2FAQRLink($request, $id);
        else
            return $this->response(951);
    }

    public function vertify2FAQRLink(Request $request, int $id)
    {
        if(Ablilty::isMySalesforce($request, $id))    
            return $this->_vertify2FAQRLink($request, $this->salesforces->where('id', $id));   
        else
            return $this->response(951);
    }

    public function mchtBatchFee(Request $request, int $id)
    {
        if(Ablilty::isSalesforce($request) || Ablilty::isOperator($request))
        {
            $validated = $request->validate(['mcht_batch_fee'=>'required']);
            $res = $this->salesforces
                ->where('id', $id)
                ->update(['mcht_batch_fee' => $request->mcht_batch_fee/100]);
            return $this->response($res ? 1 : 990);            
        }
        else
            return $this->response(951);
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
            foreach($datas as $data)
            {
                [$result, $msg] = AuthPasswordChange::registerValidate($data['user_name'], $data['user_pw']);
                if($result === false)
                    return $this->extendResponse(954, $data['user_name']." ".$msg, []);
            }

            $salesforces = $datas->map(function ($data) use($current, $brand_id) {
                $data['user_pw'] = Hash::make($data['user_pw'].$current);
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
