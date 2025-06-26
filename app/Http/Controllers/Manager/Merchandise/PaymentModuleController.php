<?php
namespace App\Http\Controllers\Manager\Merchandise;

use App\Models\Transaction;
use App\Models\Merchandise\PaymentModule;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Traits\StoresTrait;

use App\Http\Controllers\Manager\Service\BrandInfo;
use App\Http\Controllers\Manager\CodeGenerator\TidGenerator;
use App\Http\Controllers\Manager\CodeGenerator\MidGenerator;
use App\Http\Controllers\Manager\Salesforce\UnderSalesforce;
use App\Http\Controllers\Manager\PaymentModule\VisiableSetter;

use App\Http\Requests\Manager\Merchandise\PayModuleRequest;
use App\Http\Requests\Manager\IndexRequest;

use App\Http\Controllers\Utils\ChartFormat;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Ablilty\Ablilty;
use App\Http\Controllers\Ablilty\EditAbleWorkTime;
use App\Http\Controllers\Ablilty\ActivityHistoryInterface;


/**
 * @group Payment Module API
 *
 * 결제모듈 API 입니다.
 */
class PaymentModuleController extends Controller
{
    use ManagerTrait, ExtendResponseTrait, StoresTrait;
    protected $pay_modules;
    protected $target;

    public function __construct(PaymentModule $pay_modules)
    {
        $this->pay_modules = $pay_modules;
        $this->target = '결제모듈';
    }

    /**
     * 차트 데이터 출력
     *
     * 가맹점 이상 가능
     */
    public function chart(Request $request)
    {
        $query = $this->commonSelect($request, true);
        $data = $this->getIndexData($request, $query, 'payment_modules.id', ['payment_modules.*'], 'payment_modules.created_at');
        return $this->response(0, ChartFormat::default($data));
    }

    public function commonSelect($request, $is_all=false)
    {
        $search = $request->input('search', '');
        $query = $this->pay_modules
            //->join('merchandises', 'payment_modules.mcht_id', '=', 'merchandises.id')
            ->where('payment_modules.brand_id', $request->user()->brand_id);
/*
        $query = globalPGFilter($query, $request, 'payment_modules');
        $query = globalSalesFilter($query, $request, 'merchandises');
        $query = globalAuthFilter($query, $request, 'merchandises');
*/
        if($is_all === false) 
            $query = $query->where('payment_modules.is_delete', false);
        if($request->has('mcht_id'))
            $query = $query->where('payment_modules.mcht_id', $request->mcht_id);
        if($request->has('module_type'))
            $query = $query->where('payment_modules.module_type', $request->module_type);
        if($request->un_use)
            $query = $query->notUseLastMonth($request->user()->brand_id);

        return $query->where(function ($query) use ($search) {
            return $query->where('payment_modules.mid', 'like', "%$search%")
                ->orWhere('payment_modules.tid', 'like', "%$search%")
                ->orWhere('payment_modules.note', 'like', "%$search%");
                //->orWhere('merchandises.mcht_name', 'like', "%$search%");
        });
    }

    /**
     * 목록출력
     *
     * 가맹점 이상 가능
     *
     * @queryParam search string 검색어(mid, tid)
     * @queryParam module_type integer 모듈타입(0,1,2,3,4,5)
     */
    public function index(IndexRequest $request)
    {
        $cols = ['payment_modules.*'];
        //$cols = UnderSalesforce::getViewableSalesCols($request, $cols);

        $query = $this->commonSelect($request);
        $data = $this->getIndexData($request, $query, 'payment_modules.id', $cols, 'payment_modules.created_at');
        foreach($data['content'] as $content) 
        {
            VisiableSetter::set($content, $request);
        }
        return $this->response(0, $data);
    }

    /**
     * 추가
     *
     * 대리점 이상 가능
     *
     */
    public function store(PayModuleRequest $request)
    {
        $brand = BrandInfo::getBrandById($request->user()->brand_id);
        // 같은 브랜드에서 똑같은 값이 존재할 떄
        $isDuplicateId = function($bid, $key, $value) {
            return $this->pay_modules
                ->where('brand_id', $bid)
                ->where('is_delete', 0)
                ->where($key, $value)
                ->exists();
        };
        $data = $request->data();
        if(EditAbleWorkTime::validate() === false)
            return $this->extendResponse(1500, '지금은 작업할 수 없습니다.');
        if($brand['pv_options']['free']['use_tid_duplicate'] && $data['tid'] !== '' && $isDuplicateId($data['brand_id'], 'tid', $data['tid']))
            return $this->extendResponse(2000, '이미 존재하는 TID 입니다.',['tid'=>$data['tid']]);
        if($brand['pv_options']['free']['use_mid_duplicate'] && $data['mid'] !== '' && $isDuplicateId($data['brand_id'], 'mid', $data['mid']))
            return $this->extendResponse(2000, '이미 존재하는 MID 입니다.',['mid'=>$data['mid']]);
        if($data['pay_window_secure_level'] >= 3)
        {
            if($brand['pv_options']['free']['bonaeja']['user_id'] === '')
                return $this->extendResponse(1999, '문자발송 플랫폼과 연동되어있지 않아 결제창 보안등급을 설정 할 수 없습니다.<br>계약 이후 사용 가능합니다.');
        }
        if($data['module_type'] == 0 && $data['serial_num'] != '')
        {
            $res = $this->pay_modules
                ->where('brand_id', $request->user()->brand_id)
                ->where('serial_num', $data['serial_num'])
                ->where('module_type', 0)
                ->where('is_delete', false)
                ->exists();
            if($res)
                return $this->extendResponse(1001, '이미 존재하는 시리얼 번호 입니다.');
        }
        $res = app(ActivityHistoryInterface::class)->add($this->target, $this->pay_modules, $data, 'note');
        if($res)
        {
            if($data['module_type'] != 0)
            {
                $this->pay_modules->where('id', $res->id)->update([
                    'pay_key' => $this->getNewPayKey($res->id),
                    'sign_key' => $this->getNewPayKey($res->id)
                ]);
            }
            return $this->response(1, ['id' => $res->id, 'mcht_id' => $data['mcht_id']]);
        }
        else
            return $this->response(990);
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
        $data = $this->pay_modules->where('id', $id)->first();
        if($data)
        {
            if(Ablilty::isBrandCheck($request, $data->brand_id) === false)
                return $this->response(951);
            if(Ablilty::isOperator($request) || Ablilty::isMyMerchandise($request, $data->mcht_id) || Ablilty::isUnderMerchandise($request, $data->mcht_id))
            {
                VisiableSetter::set($data, $request);
                return $this->response(0, $data);
            }
            else
                return $this->response(951);
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
    public function update(PayModuleRequest $request, int $id)
    {
        // 같은 브랜드에서 똑같은 값이 존재할 떄
        $isDuplicateId = function($bid, $pid, $key, $value) {
            return $this->pay_modules
                ->where('brand_id', $bid)
                ->where('id', '!=', $pid)
                ->where('is_delete', 0)
                ->where($key, $value)
                ->exists();
        };  // tid 중복해서 사용하는 브랜드들은 ..?
        $query = $this->pay_modules->where('id', $id);
        $module = $query->first();
        $data = $request->data();

        if(Ablilty::isBrandCheck($request, $module->brand_id) === false)
            return $this->response(951);
        if(EditAbleWorkTime::validate() === false)
            return $this->extendResponse(1500, '지금은 작업할 수 없습니다.');
        if(Ablilty::isOperator($request) || Ablilty::isUnderMerchandise($request, $data['mcht_id']))
        {
            $brand = BrandInfo::getBrandById($request->user()->brand_id);
            if($brand['pv_options']['free']['use_tid_duplicate'] && $data['tid'] != '' && $isDuplicateId($data['brand_id'], $id, 'tid', $data['tid']))
                return $this->extendResponse(2000, '이미 존재하는 TID 입니다.',['mid' => $data['tid']]);
            if($brand['pv_options']['free']['use_mid_duplicate'] && $data['mid'] != '' && $isDuplicateId($data['brand_id'], $id, 'mid', $data['mid']))
                return $this->extendResponse(2000, '이미 존재하는 MID 입니다.',['mid' => $data['mid']]);
            if($data['pay_window_secure_level'] >= 3)
            {
                $brand = BrandInfo::getBrandById($request->user()->brand_id);
                if($brand['pv_options']['free']['bonaeja']['user_id'] === '')
                    return $this->extendResponse(1999, '문자발송 플랫폼과 연동되어있지 않아 결제창 보안등급을 설정 할 수 없습니다.<br>계약 이후 사용 가능합니다.');
            }
            if($data['module_type'] == 0 && $data['serial_num'] != '')
            {
                $res = $this->pay_modules
                    ->where('brand_id', $request->user()->brand_id)
                    ->where('serial_num', $data['serial_num'])
                    ->where('id', '!=', $id)
                    ->where('is_delete', false)
                    ->exists();
                if($res)
                    return $this->extendResponse(1001, '이미 존재하는 시리얼 번호 입니다.');
            }
            $row = app(ActivityHistoryInterface::class)->update($this->target, $query, $data, 'note');
            if($row)
                return $this->response(1, ['id' => $id, 'mcht_id' => $data['mcht_id']]);
            else
                return $this->response(990);

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
        $query = $this->pay_modules->where('id', $id);
        $data = $query->first();
        if($data)
        {
            if(Ablilty::isBrandCheck($request, $data->brand_id) === false)
                return $this->response(951);
            if(EditAbleWorkTime::validate() === false)
                return $this->extendResponse(1500, '지금은 작업할 수 없습니다.');
            if(Ablilty::isOperator($request) || Ablilty::isUnderMerchandise($request, $data->mcht_id))
            {
                $row = app(ActivityHistoryInterface::class)->destory($this->target, $query, 'note');
                return $this->response(1, ['id' => $id]);
            }
            else
                return $this->response(951);
        }
        else
            return $this->response(1000);
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
        if(Ablilty::isOperator($request) === false && isset($request->mcht_id) === false)
            return $this->response(951);
        if(Ablilty::isMerchandise($request) && (Ablilty::isMyMerchandise($request, $request->mcht_id) === false))
            return $this->response(951);
        else
        {
            $request->merge([
                'page' => 1,
                'page_size' => 999999,
            ]);
            $cols = ['payment_modules.*'];
            $query = $this->commonSelect($request);
            $data = $this->getIndexData($request, $query, 'payment_modules.id', $cols, 'payment_modules.created_at');
            
            foreach($data['content'] as $content) 
            {
                VisiableSetter::set($content, $request);
            }
            return $this->response(0, $data);    
        }
    }
    
    /**
     * TID 발급
     */
    public function tidCreate(Request $request)
    {
        $tid = TidGenerator::create($request->pg_type);
        return $this->response(0, ['tid'=>$tid]);    
    }
    
    /**
     * MID 발급
     */
    public function midCreate(Request $request)
    {
        $mid = MidGenerator::create($request->mid_code);
        return $this->response(0, ['mid'=>$mid]);    
    }

    /**
     * MID 대량발급
     */
    public function midBulkCreate(Request $request)
    {
        $new_mids = MidGenerator::bulkCreate($request->mid_code, $request->pay_mod_count);        
        return $this->response(0, ['new_mids'=>$new_mids]);    
    }

    /**
     * TID 대량발급
     */
    public function tidBulkCreate(Request $request)
    {
        $new_pg_tids = [];
        for ($i=0; $i <count($request->groups); $i++) 
        {
            $new_tids = TidGenerator::bulkCreate($request->groups[$i]['pg_type'], $request->groups[$i]['count']);
            $new_pg_tids[] = [
                'pg_id'     => $request->groups[$i]['pg_id'],
                'new_tids' => $new_tids,
            ];
        }
        return $this->response(0, $new_pg_tids);    
    }

    
    public function getNewPayKey($id)
    {
        return $id.Str::random(64 - strlen((string)$id));
    }

    /**
     * PAY KEY 발급
     */
    public function payKeyCreate(Request $request)
    {
        $pay_key = $this->getNewPayKey($request->id);
        $res = $this->pay_modules
            ->where('id', $request->id)
            ->update(['pay_key' => $pay_key]);
        return $this->response(0, ['pay_key'=>$pay_key]);    
    }

    /**
     * 서명 KEY 발급
     */
    public function signKeyCreate(Request $request)
    {
        $sign_key = $this->getNewPayKey($request->id);
        $res = $this->pay_modules
            ->where('id', $request->id)
            ->update(['sign_key' => $sign_key]);
        return $this->response(0, ['sign_key' => $sign_key]);    
    }
}
