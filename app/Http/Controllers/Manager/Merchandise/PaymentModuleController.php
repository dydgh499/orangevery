<?php
namespace App\Http\Controllers\Manager\Merchandise;

use App\Models\Transaction;
use App\Models\Merchandise\PaymentModule;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Traits\StoresTrait;
use App\Http\Traits\Salesforce\UnderSalesTrait;

use App\Http\Controllers\Manager\CodeGenerator\TidGenerator;
use App\Http\Controllers\Manager\CodeGenerator\MidGenerator;

use App\Http\Requests\Manager\BulkRegister\BulkPayModuleRequest;
use App\Http\Requests\Manager\BulkRegister\BulkPayModulePGRequest;
use App\Http\Requests\Manager\Merchandise\PayModuleRequest;
use App\Http\Requests\Manager\IndexRequest;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Enums\HistoryType;
use App\Http\Controllers\Ablilty\Ablilty;


/**
 * @group Payment Module API
 *
 * 결제모듈 API 입니다.
 */
class PaymentModuleController extends Controller
{
    use ManagerTrait, ExtendResponseTrait, StoresTrait, UnderSalesTrait;
    protected $pay_modules;
    protected $target;

    public function __construct(PaymentModule $pay_modules)
    {
        $this->pay_modules = $pay_modules;
        $this->target = '결제모듈';
        $this->imgs = [];
    }

    /**
     * 차트 데이터 출력
     *
     * 가맹점 이상 가능
     */
    public function chart(Request $request)
    {
        $cols = ['payment_modules.*'];
        $query = $this->commonSelect($request, true);
        $data = $this->getIndexData($request, $query, 'payment_modules.id', $cols, 'payment_modules.created_at');
        $chart = getDefaultUsageChartFormat($data);
        return $this->response(0, $chart);
    }

    private function commonSelect($request, $is_all=false)
    {
        $search = $request->input('search', '');
        $query = $this->pay_modules
            ->join('merchandises', 'payment_modules.mcht_id', '=', 'merchandises.id')
            ->where('payment_modules.brand_id', $request->user()->brand_id);
        if($is_all == false) 
        {
            $query = $query->where('merchandises.is_delete', false)
            ->where('payment_modules.is_delete', false);
        }
            
        $query = globalPGFilter($query, $request, 'payment_modules');
        $query = globalSalesFilter($query, $request, 'merchandises');
        $query = globalAuthFilter($query, $request, 'merchandises');

        if($request->has('mcht_id'))
            $query = $query->where('payment_modules.mcht_id', $request->mcht_id);
        if($request->has('module_type'))
            $query = $query->where('payment_modules.module_type', $request->module_type);

        if($request->un_use)
            $query = $query->notUseLastMonth($request->user()->brand_id);

        return $query->where(function ($query) use ($search) {
            return $query->where('payment_modules.mid', 'like', "%$search%")
                ->orWhere('payment_modules.tid', 'like', "%$search%")
                ->orWhere('payment_modules.note', 'like', "%$search%")
                ->orWhere('merchandises.mcht_name', 'like', "%$search%");
        });
    }

    /**
     * 목록출력
     *
     * 가맹점 이상 가능
     *
     * @queryParam search string 검색어(mid, tid)
     * @queryParam module_type integer 모듈타입(0,1,2,3,4)
     */
    public function index(IndexRequest $request)
    {
        $cols = ['payment_modules.*', 'merchandises.mcht_name'];
        $cols = $this->getViewableSalesCols($request, $cols);

        $query = $this->commonSelect($request);
        $data = $this->getIndexData($request, $query, 'payment_modules.id', $cols, 'payment_modules.created_at');
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
        // 같은 브랜드에서 똑같은 값이 존재할 떄
        $isDuplicateId = function($bid, $key, $value) {
            return $this->pay_modules
                ->where('brand_id', $bid)
                ->where($key, $value)
                ->exists();
        };

        $data = $request->data();
        
        if(Ablilty::isEditAbleTime() === false)
            return $this->extendResponse(1500, '지금은 작업할 수 없습니다.');
        if($request->use_tid_duplicate && $data['tid'] != '' && $isDuplicateId($data['brand_id'], 'tid', $data['tid']))
            return $this->extendResponse(2000, '이미 존재하는 TID 입니다.',['tid'=>$data['tid']]);
        if($request->use_mid_duplicate && $data['tid'] != '' && $isDuplicateId($data['brand_id'], 'mid', $data['mid']))
            return $this->extendResponse(2000, '이미 존재하는 MID 입니다.',['mid'=>$data['mid']]);
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

        $res = DB::transaction(function () use($data) {
            $res = $this->pay_modules->create($data);
            if($data['module_type'] != 0)
            {
                $this->pay_modules
                    ->where('id', $res->id)
                    ->update(['pay_key' => $this->getNewPayKey($res->id)]);
            }
            return $res;
        });

        operLogging(HistoryType::CREATE, $this->target, [], $data, $data['note']."(#".$res->id.")");
        return $this->response($res ? 1 : 990, ['id'=>$res->id, 'mcht_id'=>$data['mcht_id']]);
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
            if((Ablilty::isMyMerchandise($request, $data->mcht_id) || Ablilty::isSalesforce($request) || Ablilty::isOperator($request)))
                return $this->response(0, $data);
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
                ->where($key, $value)
                ->exists();
        };  // tid 중복해서 사용하는 브랜드들은 ..?

        $data = $request->data();
        
        if(Ablilty::isEditAbleTime() === false)
            return $this->extendResponse(1500, '지금은 작업할 수 없습니다.');
        if($request->use_tid_duplicate && $data['tid'] != '' && $isDuplicateId($data['brand_id'], $id, 'tid', $data['tid']))
            return $this->extendResponse(2000, '이미 존재하는 TID 입니다.',['mid'=>$data['tid']]);
        if($request->use_mid_duplicate && $data['tid'] != '' && $isDuplicateId($data['brand_id'], 'mid', $data['mid']))
            return $this->extendResponse(2000, '이미 존재하는 MID 입니다.',['mid'=>$data['mid']]);            

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
        $before = $this->pay_modules->where('id', $id)->first();

        if(Ablilty::isBrandCheck($request, $before->brand_id) === false)
            return $this->response(951);
        else
        {
            $res = $this->pay_modules->where('id', $id)->update($data);

            operLogging(HistoryType::UPDATE, $this->target, $before, $data, $data['note']."(#".$id.")");
            return $this->response($res ? 1 : 990, ['id'=>$id, 'mcht_id'=>$data['mcht_id']]);    
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
            $data = $this->pay_modules->where('id', $id)->first();
            if($data)
            {
                if(Ablilty::isBrandCheck($request, $data->brand_id) === false)
                    return $this->response(951);
                if(Ablilty::isEditAbleTime() === false)
                    return $this->extendResponse(1500, '지금은 작업할 수 없습니다.');
                $res = $this->delete($this->pay_modules->where('id', $id));            
                operLogging(HistoryType::DELETE, $this->target, $data, ['id' => $id], $data->note);
                return $this->response($res, ['id'=>$id, 'mcht_id'=>$data->mcht_id]);    
            }
            else
                return $this->response(1000);
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
            'page_size' => 99999999,
        ]);
        if(Ablilty::isSalesforce($request) || Ablilty::isOperator($request))
            $cols = ['payment_modules.*'];
        else
        {
            $cols = [
                'payment_modules.show_pay_view',
                'payment_modules.id', 'payment_modules.mcht_id', 'payment_modules.installment',
                'payment_modules.mid', 'payment_modules.tid', 'payment_modules.pg_id', 'payment_modules.ps_id',
                'payment_modules.module_type', 'payment_modules.settle_fee', 'payment_modules.settle_type',
                'payment_modules.terminal_id', 'payment_modules.note', 'payment_modules.is_old_auth', 'payment_modules.installment', 
            ];
        }
        $query = $this->commonSelect($request);
        $data = $this->getIndexData($request, $query, 'payment_modules.id', $cols, 'payment_modules.created_at');
        return $this->response(0, $data);
    }
    
    /**
     * 결제모듈 대량등록
     *
     * 운영자 이상 가능
     */
    public function bulkRegister(BulkPayModuleRequest $request)
    {
        $current = date('Y-m-d H:i:s');
        $brand_id = $request->user()->brand_id;
        $datas = $request->data();
        if(count($datas) > 1000)
            return $this->extendResponse(1000, '결제모듈은 한번에 최대 1000개까지 등록할 수 있습니다.');
        else
        {
            $pay_modules = $datas->map(function ($data) use($current, $brand_id) {
                $data['brand_id'] = $brand_id;
                $data['created_at'] = $current;
                $data['updated_at'] = $current;
                return $data;
            })->toArray();

            $res = $this->manyInsert($this->pay_modules, $pay_modules);
            return $this->response($res ? 1 : 990);
        }
    }

    /**
     * 구간 일괄변경
     *
     * 운영자 이상 가능
     */
    public function bulkRegisterPG(BulkPayModulePGRequest $request)
    {
        $datas = $request->data();
        foreach($datas as $data)
        {
            $this->pay_modules->where('mcht_id', $data['mcht_id'])
                ->update([
                    'pg_id' => $data['pg_id'],
                    'ps_id' => $data['ps_id'],
                ]);
        }
        return $this->response(1);        
    }

    /**
     * 영수증 정보조회
     *
     * @urlParam id integer required 유저 PK
     */
    public function saleSlip(Request $request, int $id)
    {
        $cols = [
            'merchandises.addr',
            'merchandises.business_num',
            'merchandises.resident_num',
            'merchandises.mcht_name',
            'merchandises.nick_name',
            'merchandises.is_show_fee',
            'merchandises.use_saleslip_prov',
            'merchandises.use_saleslip_sell',
        ];
        $mcht = $this->pay_modules
            ->join('merchandises', 'merchandises.id', '=', 'payment_modules.mcht_id')
            ->where('payment_modules.id', $id)
            ->first($cols);
        return $this->response(0, $mcht);        
    }

    /**
     * TID 발급
     */
    public function tidCreate(Request $request)
    {
        //0523070000 pg(2) + ym(2) + idx(4)
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
}
