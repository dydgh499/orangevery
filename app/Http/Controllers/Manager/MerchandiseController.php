<?php

namespace App\Http\Controllers\Manager;

use App\Models\Salesforce;
use App\Models\Merchandise;
use App\Models\PaymentModule;
use App\Models\NotiUrl;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Traits\StoresTrait;

use App\Http\Requests\Manager\BulkRegister\BulkMerchandiseRequest;
use App\Http\Requests\Manager\MerchandiseRequest;
use App\Http\Requests\Manager\IndexRequest;
use Illuminate\Support\Facades\Hash;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Enums\HistoryType;


use App\Http\Controllers\Log\FeeChangeHistoryController;
use App\Models\Log\MchtFeeChangeHistory;
use App\Models\Log\SfFeeChangeHistory;

/**
 * @group Merchandise API
 *
 * 가맹점 관리 메뉴에서 사용될 API 입니다. 가맹점 이상권한이 요구됩니다.
 */
class MerchandiseController extends Controller
{
    use ManagerTrait, ExtendResponseTrait, StoresTrait;
    protected $merchandises, $payModules;
    protected $target;
    protected $imgs;
    protected $pay_mod_cols;

    public function __construct(Merchandise $merchandises, PaymentModule $payModules)
    {
        $this->merchandises = $merchandises;
        $this->payModules = $payModules;
        $this->target = '가맹점';
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
                500, 500, 500, 500, 120
            ],
        ];
        $this->pay_mod_cols = ['mcht_id', 'mid', 'tid', 'module_type', 'pg_id'];
    }

    public function chart(Request $request)
    {
        $request->merge([
            'page' => 1,
            'page_size' => 99999999,
        ]);
        $data = $this->commonSelect($request, true);
        $chart = getDefaultUsageChartFormat($data);
        return $this->response(0, $chart);
    }

    private function byPayModules($request, $is_all)
    {
        $query = $this->merchandises
            ->join('payment_modules', 'merchandises.id', '=', 'payment_modules.mcht_id')
            ->where('payment_modules.is_delete', false)
            ->distinct('payment_modules.mcht_id');

        $query = globalPGFilter($query, $request, 'payment_modules');
        $query = $this->mchtCommonFilter($query, $request, 'merchandises', $is_all);
        return $this->getIndexData($request, $query, 'merchandises.id', ['merchandises.*'], 'merchandises.created_at');
    }

    private function byNormalIndex($request, $is_all)
    {
        $query = $this->merchandises;
        $query = $this->mchtCommonFilter($query, $request, 'merchandises', $is_all);
        return $this->getIndexData($request, $query, 'id');
    }

    private function mchtCommonFilter($query, $request, $parent, $is_all)
    {
        $full_parent = $parent != '' ? $parent."." : '';
        $search = $request->input('search', '');

        $query = globalSalesFilter($query, $request, $parent);
        $query = globalAuthFilter($query, $request, $parent);
        $query = $query
                ->where($full_parent.'brand_id', $request->user()->brand_id)
                ->where(function ($query) use ($search, $full_parent) {
            return $query->where('mcht_name', 'like', "%$search%")
                ->orWhere($full_parent.'user_name', 'like', "%$search%")
                ->orWhere($full_parent.'phone_num', 'like', "%$search%")
                ->orWhere($full_parent.'business_num', 'like', "%$search%")
                ->orWhere($full_parent.'nick_name', 'like', "%$search%");
        });

        if($is_all == false)
            $query = $query->where($full_parent.'is_delete', false);
        return $query;
    }
    
    private function mappingPayModules($data, $pay_modules)
    {
        $module_mcht_ids = [];
        foreach ($pay_modules as $module) {
            $module_mcht_ids[$module->mcht_id][] = $module;
        }
        foreach($data['content'] as $content) 
        {
            $my_modules = isset($module_mcht_ids[$content->id]) ? collect($module_mcht_ids[$content->id]) : collect();
            $content->mids = $my_modules->pluck('mid')->values()->toArray();
            $content->tids = $my_modules->pluck('tid')->values()->toArray();
            $content->module_types = $my_modules->pluck('module_type')->values()->toArray();    
            $content->pgs = $my_modules->pluck('pg_id')->values()->toArray();
            $content->setFeeFormatting(true);
        }
        return $data;
    }

    private function commonSelect($request, $is_all=false)
    {
        $cond_1 = $request->pg_id || $request->ps_id || $request->terminal_id;
        $cond_2 = zeroCheck($request, 'settle_type') || zeroCheck($request, 'mcht_settle_type') || zeroCheck($request, 'module_type');
        if($cond_1 || $cond_2)
            $data = $this->byPayModules($request, $is_all);
        else 
            $data = $this->byNormalIndex($request, $is_all);
        // payment modules sections
        $mcht_ids = collect($data['content'])->pluck('id')->all();
        $pay_modules = $this->payModules
            ->where('brand_id', $request->user()->brand_id)
            ->where('is_delete', false)
            ->whereIn('mcht_id', $mcht_ids)
            ->get($this->pay_mod_cols);
        
        return $this->mappingPayModules($data, $pay_modules);    
    }

    /**
     * 목록출력
     *
     * 가맹점 이상 가능
     *
     * @queryParam search string 검색어(가맹점상호, 휴대폰번호, 대표자명)
     */
    public function index(IndexRequest $request)
    {
        $data = $this->commonSelect($request);
        $sales_ids      = globalGetUniqueIdsBySalesIds($data['content']);
        $salesforces    = globalGetSalesByIds($sales_ids);
        $data['content'] = globalMappingSales($salesforces, $data['content']);
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
                ->where('is_delete', false)
                ->where(function ($query) use ($request) {
                    return $query->where('user_name', $request->user_name)
                        ->orWhere('mcht_name', $request->mcht_name);
                })
                ->first();
            if(!$user)
            {
                $user = $request->data();
                // 수수료율 정보는 추가시에만 적용되어야함
                $user['sales0_id'] = $request->input('sales0_id', null);
                $user['sales1_id'] = $request->input('sales1_id', null);
                $user['sales2_id'] = $request->input('sales2_id', null);
                $user['sales3_id'] = $request->input('sales3_id', null);
                $user['sales4_id'] = $request->input('sales4_id', null);
                $user['sales5_id'] = $request->input('sales5_id', null);
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
                $res = $this->merchandises->create($user);
                
                operLogging(HistoryType::CREATE, $this->target, $user, $user['mcht_name']);
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
            $data = $this->merchandises->where('id', $id)
                ->with(['regularCreditCards'])
                ->first();
            $data->setFeeFormatting(true);
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
                        
            operLogging(HistoryType::UPDATE, $this->target, $data, $data['mcht_name']);
            return $this->response($res ? 1 : 990, ['id'=>$id]);
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
        $res = $this->delete($this->merchandises->where('id', $id));
        $res = $this->delete($this->payModules->where('mcht_id', $id));
        $res = $this->delete(NotiUrl::where('mcht_id', $id));

        $data = $this->merchandises->where('id', $id)->first(['mcht_name']);

        operLogging(HistoryType::DELETE, $this->target, ['id' => $id], $data->mcht_name);
        return $this->response($res ? 1 : 990, ['id'=>$id]);
    }

    /**
     * 전체목록출력
     *
     * 가맹점 이상 가능
     *
     * @queryParam search string 검색어(유저 ID)
     */
    public function all(Request $request)
    {
        $request->merge([
            'page' => 1,
            'page_size' => 99999999,
        ]);
        $cols = [
            'merchandises.id', 'merchandises.mcht_name',
            'merchandises.sales5_id', 'merchandises.sales5_fee',
            'merchandises.sales4_id', 'merchandises.sales4_fee',
            'merchandises.sales3_id', 'merchandises.sales3_fee',
            'merchandises.sales2_id', 'merchandises.sales2_fee',
            'merchandises.sales1_id', 'merchandises.sales1_fee',
            'merchandises.sales0_id', 'merchandises.sales0_fee',
            'merchandises.hold_fee', 'merchandises.trx_fee',
            'merchandises.custom_id', 'merchandises.addr',
            'merchandises.business_num', 'merchandises.resident_num',
            'merchandises.use_saleslip_prov', 'merchandises.use_saleslip_sell', 'merchandises.use_regular_card'
        ];
        $data = $this->commonSelect($request);
        $sales_ids      = globalGetUniqueIdsBySalesIds($data['content']);
        $salesforces    = globalGetSalesByIds($sales_ids, false);
        $data['content'] = globalMappingSales($salesforces, $data['content']);
        return $this->response(0, $data);
    }

    public function passwordChange(Request $request)
    {
        $validated = $request->validate(['id'=>'required|integer', 'user_pw'=>'required']);
        $res = $this->merchandises
            ->where('id', $request->id)
            ->update(['user_pw' => Hash::make($request->user_pw)]);
        return $this->response($res ? 1 : 990);        
    }

    public function bulkRegister(BulkMerchandiseRequest $request)
    {
        $current = date('Y-m-d H:i:s');
        $brand_id = $request->user()->brand_id;
        $datas = $request->data();

        $getExistitems = function($datas, $brand_id, $col) {
            $items = $datas->pluck($col)->values()->toArray();
            return $this->merchandises
                    ->where('brand_id', $brand_id)
                    ->where('is_delete', false)
                    ->whereIn($col, $items)
                    ->pluck($col)->toArray();
        };

        $exist_names = $getExistitems($datas, $brand_id, 'user_name');
        $exist_mchts = $getExistitems($datas, $brand_id, 'mcht_name');
        if(count($exist_names))
            return $this->extendResponse(1000, join(',', $exist_names).'는 이미 존재하는 아이디 입니다.');
        else if(count($exist_names))
            return $this->extendResponse(1000, join(',', $exist_mchts).'는 이미 존재하는 상호 입니다.');
        else
        {
            $merchandises = $datas->map(function ($data) use($current, $brand_id) {
                $data['user_pw'] = Hash::make($data['user_pw']);
                $data['brand_id'] = $brand_id;
                $data['created_at'] = $current;
                $data['updated_at'] = $current;
                return $data;
            })->toArray();
            $res = $this->manyInsert($this->merchandises, $merchandises);
            return $this->response($res ? 1 : 990);
        }
    }

    /**
     * 영수증 정보조회
     *
     * @urlParam id integer required 유저 PK
     * @return \Illuminate\Http\JsonResponse
     */
    public function saleSlip(Request $request, $id)
    {
        $cols = [
            'id', 'addr', 'business_num', 'resident_num', 'mcht_name', 'user_name',
            'nick_name', 'is_show_fee', 'use_saleslip_prov', 'use_saleslip_sell', 'use_regular_card'
        ];
        $data = $this->merchandises
            ->where('id', $id)
            ->first($cols);
        return $this->response(0, $data);
    }
}
