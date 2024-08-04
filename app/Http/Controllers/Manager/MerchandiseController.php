<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Ablilty\Ablilty;
use App\Http\Controllers\Ablilty\EditAbleWorkTime;

use App\Models\Operator;
use App\Models\Merchandise;
use App\Http\Controllers\Ablilty\AbnormalConnection;
use App\Models\Merchandise\PaymentModule;
use App\Models\Merchandise\NotiUrl;

use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Traits\StoresTrait;

use App\Http\Controllers\Manager\Salesforce\UnderSalesforce;
use App\Http\Requests\Manager\BulkRegister\BulkMerchandiseRequest;
use App\Http\Requests\Manager\MerchandiseRequest;
use App\Http\Requests\Manager\IndexRequest;

use App\Http\Controllers\Auth\AuthPasswordChange;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Manager\Service\BrandInfo;
use App\Http\Controllers\FirstSettlement\SysLink;
use App\Http\Controllers\Utils\ChartFormat;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Enums\HistoryType;


/**
 * @group Merchandise API
 *
 * 가맹점 관리 메뉴에서 사용될 API 입니다. 가맹점 이상권한이 요구됩니다.
 */
class MerchandiseController extends Controller
{
    use ManagerTrait, ExtendResponseTrait, StoresTrait;
    protected $merchandises, $pay_modules;
    protected $target;
    protected $imgs;

    public function __construct(Merchandise $merchandises, PaymentModule $pay_modules)
    {
        $this->merchandises = $merchandises;
        $this->pay_modules = $pay_modules;
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
    }

    /**
     * 차트 데이터 출력
     *
     * 가맹점 이상 가능
     */
    public function chart(Request $request)
    {
        $data = $this->commonSelect($request, true);
        return $this->response(0, ChartFormat::default($data));
    }

    private function byPayModules($request, $is_all)
    {
        $query = $this->merchandises
            ->join('payment_modules', 'merchandises.id', '=', 'payment_modules.mcht_id')
            ->where('payment_modules.is_delete', false)
            ->distinct('payment_modules.mcht_id');

        $query = globalPGFilter($query, $request, 'payment_modules');
        $query = $this->mchtCommonFilter($query, $request, 'merchandises', $is_all);
        return $this->getIndexData($request, $query, 'merchandises.id', ['merchandises.*'], 'merchandises.id', false);
    }

    private function byNormalIndex($request, $is_all)
    {
        $query = $this->merchandises;
        $query = $this->mchtCommonFilter($query, $request, 'merchandises', $is_all);
        return $this->getIndexData($request, $query, 'id', [], 'id', false);
    }

    private function mchtCommonFilter($query, $request, $parent, $is_all)
    {
        $search = $request->input('search', '');

        $query = globalSalesFilter($query, $request, $parent);
        $query = globalAuthFilter($query, $request, $parent);
        $query = $query
                ->where($parent.'.brand_id', $request->user()->brand_id)
                ->where(function ($query) use ($search, $parent) {
                    return $query->where($parent.'.mcht_name', 'like', "%$search%")
                        ->orWhere($parent.'.user_name', 'like', "%$search%")
                        ->orWhere($parent.'.phone_num', 'like', "%$search%")
                        ->orWhere($parent.'.business_num', 'like', "%$search%")
                        ->orWhere($parent.'.nick_name', 'like', "%$search%")
                        ->orWhere($parent.'.acct_num', 'like', "%$search%")
                        ->orWhere($parent.'.acct_name', 'like', "%$search%");
                });
        if($request->is_lock)
            $query = $query->where($parent.'.is_lock', 1);
        if($request->input('settle_hold', 0))
            $query = $query->whereNotNull($parent.'.settle_hold_s_dt');
        if($is_all == false)
            $query = $query->where($parent.'.is_delete', false);
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
            $content->serial_nums  = $my_modules->pluck('serial_num')->values()->toArray();
            $content->settle_types = $my_modules->pluck('settle_type')->values()->toArray();
            $content->module_types = $my_modules->pluck('module_type')->values()->toArray();    
            $content->mids = $my_modules->pluck('mid')->values()->toArray();
            $content->tids = $my_modules->pluck('tid')->values()->toArray();
            $content->pgs = $my_modules->pluck('pg_id')->values()->toArray();
            $content->setFeeFormatting(true);
        }
        return $data;
    }

    private function commonSelect($request, $is_all=false)
    {
        $cond_1 = $request->pg_id || $request->ps_id || $request->terminal_id;
        $cond_2 = $request->settle_type !== null || $request->mcht_settle_type !== null || $request->module_type != null;
        if($cond_1 || $cond_2)
            $data = $this->byPayModules($request, $is_all);
        else 
            $data = $this->byNormalIndex($request, $is_all);
        
        // payment modules sections
        $mcht_ids = collect($data['content'])->sortByDesc('id')->pluck('id')->all();
        $pay_modules = $this->pay_modules
            ->where('brand_id', $request->user()->brand_id)
            ->where('is_delete', false)
            ->whereIn('mcht_id', $mcht_ids)
            ->orderby('id', 'desc')
            ->get(['mcht_id', 'mid', 'tid', 'module_type', 'pg_id', 'settle_type', 'serial_num']);
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
        $data           = $this->commonSelect($request);
        $sales_ids      = globalGetUniqueIdsBySalesIds($data['content']);
        $salesforces    = globalGetSalesByIds($sales_ids);
        $data['content'] = globalMappingSales($salesforces, $data['content']);
        foreach($data['content'] as $content)
        {
            $content = UnderSalesforce::setViewableSalesInfos($request, $content);
        }
        return $this->response(0, $data);
    }

    /**
     * 추가
     *
     * 대리점 이상 가능
     *
     * @bodyParam user_pw string 유저 패스워드
     */
    public function store(MerchandiseRequest $request)
    {
        $b_info = BrandInfo::getBrandById($request->user()->brand_id);
        $validated = $request->validate(['user_pw'=>'required']);

        if(EditAbleWorkTime::validate() === false)
            return $this->extendResponse(1500, '지금은 작업할 수 없습니다.');
        if($this->isExistMutual($this->merchandises, $request->user()->brand_id, 'mcht_name', $request->mcht_name))
            return $this->extendResponse(1001, __("validation.already_exsit", ['attribute'=>'상호']));
        else
        {
            if($this->isExistUserName($request->user()->brand_id, $request->user_name))
                return $this->extendResponse(1001, __("validation.already_exsit", ['attribute'=>'아이디']));
            else
            {
                $current = date("Y-m-d H:i:s");
                $user = $request->data();
                
                [$result, $msg] = AuthPasswordChange::registerValidate($user['user_name'], $request->user_pw);
                if($result === false)
                    return $this->extendResponse(954, $msg, []);
                else
                {
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
                    $user['user_pw'] = Hash::make($request->user_pw.$current);
                    $user['created_at'] = $current;

                    if($b_info['pv_options']['paid']['use_syslink'] && Ablilty::isOperator($request) && (int)$request->use_syslink)
                    {
                        $res = SysLink::create($user);
                        if($res['code'] !== 'SUCCESS')
                            return $this->extendResponse(1999, $res['message']);
                    }

                    $res = $this->merchandises->create($user);
                    operLogging(HistoryType::CREATE, $this->target, [], $user, $user['mcht_name']);
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
        $with = [];
        $b_info = BrandInfo::getBrandById($request->user()->brand_id);
        if($b_info['pv_options']['paid']['use_regular_card'])
            array_push($with, 'regularCreditCards');
        if($b_info['pv_options']['paid']['use_specified_limit']);
            array_push($with, 'specifiedTimeDisableLimitPayments');

        $data = $this->merchandises->where('id', $id)->with($with)->first();
        if($data)
        {
            $data->setFeeFormatting(true);
            if(Ablilty::isBrandCheck($request, $data->brand_id) === false)
                return $this->response(951);
            if(Ablilty::isOperator($request) || Ablilty::isMyMerchandise($request, $id) || Ablilty::isUnderMerchandise($request, $id))
            {
                if($b_info['pv_options']['paid']['use_syslink'] && Ablilty::isOperator($request))
                    $data['syslink'] = SysLink::show($data['user_name']);
                return $data ? $this->response(0, $data) : $this->response(1000);    
            }
            else
            {   // URL 조작 (가맹점인데 다른가맹점 조회하려할 시) 영업점일때 자신아래 영업점이 아닌경우?
                AbnormalConnection::tryParameterModulationApproach();
                return $this->response(951);
            }
            
        }
        else
            return $this->response(1000);
    }

    /**
     * 업데이트
     * 
     * 가맹점 이상 가능
     *
     * @urlParam id integer required 유저 PK
     */
    public function update(MerchandiseRequest $request, int $id)
    {
        if(Ablilty::isOperator($request) || Ablilty::isUnderMerchandise($request, $id))
        {
            $query = $this->merchandises->where('id', $id);
            $user = $query->first();
            $data = $request->data();

            if(Ablilty::isBrandCheck($request, $user->brand_id) === false)
                return $this->response(951);
            if(EditAbleWorkTime::validate() === false)
                return $this->extendResponse(1500, '지금은 작업할 수 없습니다.');
            if($this->isExistMutual($this->merchandises->where('id', '!=', $id), $request->user()->brand_id, 'mcht_name', $data['mcht_name']))
                return $this->extendResponse(1001, '이미 존재하는 상호 입니다.');
            // 변경된 아이디가 이미 존재할 떄
            if($user->user_name !== $request->user_name && $this->isExistUserName($request->user()->brand_id, $request->user_name))
                return $this->extendResponse(1001, __("validation.already_exsit", ['attribute'=>'아이디']));
            else
            {
                $data = $this->saveImages($request, $data, $this->imgs);
                // -- update syslink
                $b_info = BrandInfo::getBrandById($request->user()->brand_id);
                if($b_info['pv_options']['paid']['use_syslink'] && Ablilty::isOperator($request) && (int)$request->use_syslink)
                {
                    if($request->syslink['code'] !== 'SUCCESS')
                        $res = SysLink::create($data);
                    else
                        $res = SysLink::update($data);

                    if($res['code'] !== 'SUCCESS')
                        return $this->extendResponse(1999, $res['message']);
                }

                $res = $query->update($data);                
                operLogging(HistoryType::UPDATE, $this->target, $user, $data, $data['mcht_name']);
                return $this->response($res ? 1 : 990, ['id'=>$id]);        
            }
        }        
        else
            return $this->response(951);
        
    }

    /**
     * 단일삭제
     *
     * @urlParam id integer required 유저 PK
     */
    public function destroy(Request $request, int $id)
    {
        if(Ablilty::isOperator($request) || Ablilty::isUnderMerchandise($request, $id))
        {
            $data = $this->merchandises->where('id', $id)->first();
            if(Ablilty::isBrandCheck($request, $data->brand_id) === false)
                return $this->response(951);
            if(EditAbleWorkTime::validate() === false)
                return $this->extendResponse(1500, '지금은 작업할 수 없습니다.');

            DB::transaction(function () use($id, $data) {
                $res = $this->delete($this->pay_modules->where('mcht_id', $id));
                $res = $this->delete($this->merchandises->where('id', $id));
                $res = $this->delete(NotiUrl::where('mcht_id', $id));
                operLogging(HistoryType::DELETE, $this->target, $data, ['id' => $id], $data->mcht_name);
            });
            return $this->response(1, ['id'=>$id]);    
        }
        else
            return $this->response(951);
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
            'page_size' => 9999999,
        ]);
        $cols = ['merchandises.id', 'merchandises.mcht_name'];
        if(Ablilty::isMerchandise($request))
        {
        }
        else if(Ablilty::isSalesforce($request))
        {   // 영업자
            if($request->user()->level > 10)
                $cols[] = 'sales0_id';
            if($request->user()->level > 13)
                $cols[] = 'sales1_id';
            if($request->user()->level > 15)
                $cols[] = 'sales2_id';
            if($request->user()->level > 17)
                $cols[] = 'sales3_id';
            if($request->user()->level > 20)
                $cols[] = 'sales4_id';
            if($request->user()->level > 25)
                $cols[] = 'sales5_id';
        }
        else if(Ablilty::isOperator($request))
            $cols = array_merge($cols, ['sales5_id', 'sales4_id', 'sales3_id', 'sales2_id', 'sales1_id', 'sales0_id']);

        $query = $this->merchandises
            ->where('is_delete', false)
            ->where('brand_id', $request->user()->brand_id);

        $data = $this->getIndexData($request, $query, 'id', $cols, 'created_at');
        return $this->response(0, $data);
    }

    /**
     * 패스워드 변경
     *
     */
    public function passwordChange(Request $request, int $id)
    {
        if(Ablilty::isMyMerchandise($request, $id) || Ablilty::isOperator($request) || (Ablilty::isUnderMerchandise($request, $id) && $request->user()->is_able_unlock_mcht))
        {
            $is_me = Ablilty::isMyMerchandise($request, $id) ? true : false;
            return $this->_passwordChange($this->merchandises->where('id', $id), $request, $is_me);
        }
        else
            return $this->response(951);
    }

    /**
     * 계정잠금해제
     */
    public function unlockAccount(Request $request, int $id)
    {
        if(Ablilty::isOperator($request) || (Ablilty::isUnderMerchandise($request, $id) && $request->user()->is_able_unlock_mcht))
            return $this->_unlockAccount($this->merchandises->where('id', $id));
        else
            return $this->response(951);
    }

    /**
     * 대량등록
     *
     * 운영자 이상 가능
     */
    public function bulkRegister(BulkMerchandiseRequest $request)
    {
        $current = date('Y-m-d H:i:s');
        $brand_id = $request->user()->brand_id;
        $datas = $request->data();
        if(count($datas) > 1000)
            return $this->extendResponse(1000, '가맹점은 한번에 최대 1000개까지 등록할 수 있습니다.');
        else
        {
            $exist_names = $this->isExistBulkUserName($brand_id, $datas->pluck('user_name')->all());
            $exist_mchts = $this->isExistBulkMutual($this->merchandises, $brand_id, 'mcht_name', $datas->pluck('mcht_name')->all());
    
            if(count($exist_names))
                return $this->extendResponse(1000, join(',', $exist_names).'는 이미 존재하는 아이디 입니다.');
            else if(count($exist_mchts))
                return $this->extendResponse(1000, join(',', $exist_mchts).'는 이미 존재하는 상호 입니다.');
            else
            {
                foreach($datas as $data)
                {
                    [$result, $msg] = AuthPasswordChange::registerValidate($data['user_name'], $data['user_pw']);
                    if($result === false)
                        return $this->extendResponse(954, $data['user_name']." ".$msg, []);    
                }
    
                $merchandises = $datas->map(function ($data) use($current, $brand_id) {
                    $data['user_pw'] = Hash::make($data['user_pw'].$current);
                    $data['brand_id'] = $brand_id;
                    $data['created_at'] = $current;
                    $data['updated_at'] = $current;
                    return $data;
                })->toArray();
                $res = $this->manyInsert($this->merchandises, $merchandises);
                $mcht_ids = $this->merchandises
                        ->where('brand_id', 30)
                        ->where('created_at', $current)
                        ->pluck('id')->all();

                return $this->response($res ? 1 : 990, $mcht_ids);
            }    
        }
    }

    /**
     * 영수증 정보조회
     *
     * @urlParam id integer required 유저 PK
     */
    public function saleSlip(Request $request, int $id)
    {
        $cols = [
            'id', 'addr', 'business_num', 'resident_num', 'mcht_name', 'user_name',
            'nick_name', 'is_show_fee', 'use_saleslip_prov', 'use_saleslip_sell', 'use_regular_card',
            'tax_category_type', 'use_pay_verification_mobile',
        ];
        $data = $this->merchandises
            ->where('id', $id)
            ->first($cols);
        return $this->response(0, $data);
    }

    /**
     * 지급보류
     */
    public function setSettleHold(Request $request, $id)
    {
        $this->merchandises->where('id', $id)->update([
            'settle_hold_s_dt' => $request->settle_hold_s_dt,
            'settle_hold_reason' => $request->settle_hold_reason,
        ]);
        return $this->response(1);
    }

    /**
     * 지급보류해제
     */
    public function clearSettleHold(Request $request, $id)
    {
        $data = Operator::where('id', $request->user()->id)->first();
        if($data)
        {
            if(AuthPasswordChange::HashCheck($data, $request->user_pw))
            {
                $this->merchandises->where('id', $id)->update([
                    'settle_hold_s_dt' => null,
                    'settle_hold_reason' => '',
                ]);
                return $this->response(0);
            }
            else
                return $this->extendResponse(2000, '패스워드가 다릅니다.');
        }
        else
            return $this->response(990);
    }
}
