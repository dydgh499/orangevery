<?php

namespace App\Http\Controllers\Manager;

use App\Models\Transaction;
use App\Models\PaymentModule;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Traits\StoresTrait;

use App\Http\Requests\Manager\BulkRegister\BulkPayModuleRequest;
use App\Http\Requests\Manager\PayModuleRequest;
use App\Http\Requests\Manager\IndexRequest;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Enums\HistoryType;

class PaymentModuleController extends Controller
{
    use ManagerTrait, ExtendResponseTrait, StoresTrait;
    protected $pay_modules;
    protected $target;

    public function __construct(PaymentModule $pay_modules)
    {
        $this->pay_modules = $pay_modules;
        $this->target = '결제모듈';
        $this->imgs = [];
    }

    public function chart(Request $request)
    {
        $request->merge([
            'page' => 1,
            'page_size' => 99999999,
        ]);
        $cols = ['payment_modules.*'];
        $query = $this->commonSelect($request, true);
        $data = $this->getIndexData($request, $query, 'payment_modules.id', $cols, 'payment_modules.created_at');
        $chart = getDefaultUsageChartFormat($data);
        return $this->response(0, $chart);
    }

    private function commonSelect($request, $is_all=false)
    {
        $search = $request->input('search', '');
        $un_use = $request->input('un_use', '');
        $un_use = $un_use === 'true' ? true : false;

        $query = $this->pay_modules
            ->join('merchandises', 'payment_modules.mcht_id', '=', 'merchandises.id')
            ->where('payment_modules.brand_id', $request->user()->brand_id);
        if($is_all == false)
            $query = $query->where('payment_modules.is_delete', false);
            
        $query = globalPGFilter($query, $request, 'payment_modules');
        $query = globalSalesFilter($query, $request, 'merchandises');
        $query = globalAuthFilter($query, $request, 'merchandises');

        if($request->has('mcht_id'))
            $query = $query->where('payment_modules.mcht_id', $request->mcht_id);
        if($request->has('module_type'))
            $query = $query->where('payment_modules.module_type', $request->module_type);

        if($un_use)
        {
            $before_month = Carbon::now()->subMonths(1)->format('Y-m-d');
            $trans_pmod_ids = Transaction::where('brand_id', $request->user()->brand_id)
                ->where('trx_dt', '>=', $before_month)
                ->where('is_cancel', false)
                ->distinct()->pluck('pmod_id')->all();
            $query = $query->whereNotIn('payment_modules.id', $trans_pmod_ids);
        }

        return $query->where(function ($query) use ($search) {
            return $query->where('payment_modules.mid', 'like', "%$search%")
                ->orWhere('payment_modules.tid', 'like', "%$search%")
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
        $query = $this->commonSelect($request);
        $data = $this->getIndexData($request, $query, 'payment_modules.id', $cols, 'payment_modules.created_at');
        return $this->response(0, $data);
    }

    /**
     * 추가
     *
     * 대리점 이상 가능
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(PayModuleRequest $request)
    {
        if($request->user()->tokenCan(10))
        {
            $data = $request->data();
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

            operLogging(HistoryType::CREATE, $this->target, $data, $data['note']);
            return $this->response($res ? 1 : 990, ['id'=>$res->id, 'mcht_id'=>$data['mcht_id']]);
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
            $data = $this->pay_modules->where('id', $id)->first();
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
    public function update(PayModuleRequest $request, $id)
    {
        if($this->authCheck($request->user(), $id, 15))
        {
            $data = $request->data();
            $data['pay_key'] = $request->pay_key;

            if($data['module_type'] == 0 && $data['serial_num'] != '')
            {
                $res = $this->pay_modules
                    ->where('brand_id', $request->user()->brand_id)
                    ->where('serial_num', $data['serial_num'])
                    ->where('id', '!=', $id)
                    ->where('module_type', 0)
                    ->where('is_delete', false)
                    ->exists();
                if($res)
                    return $this->extendResponse(1001, '이미 존재하는 시리얼 번호 입니다.');
            }
            $res = $this->pay_modules->where('id', $id)->update($data);

            operLogging(HistoryType::UPDATE, $this->target, $data, $data['note']);
            return $this->response($res ? 1 : 990, ['id'=>$id, 'mcht_id'=>$data['mcht_id']]);
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
            $res = $this->delete($this->pay_modules->where('id', $id));
            $data = $this->pay_modules->where('id', $id)->first(['mcht_id', 'note']);
            
            operLogging(HistoryType::DELETE, $this->target, ['id' => $id], $data->note);
            return $this->response($res, ['id'=>$id, 'mcht_id'=>$data->mcht_id]);
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
        if($request->user()->tokenCan(13))
        {
            $cols = ['payment_modules.*'];
        }
        else
        {
            $cols = [
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
    
    public function bulkRegister(BulkPayModuleRequest $request)
    {
        $current = date('Y-m-d H:i:s');
        $brand_id = $request->user()->brand_id;
        $datas = $request->data();

        $pay_modules = $datas->map(function ($data) use($current, $brand_id) {
            $data['brand_id'] = $brand_id;
            $data['created_at'] = $current;
            $data['updated_at'] = $current;
            return $data;
        })->toArray();
        $res = $this->manyInsert($this->pay_modules, $pay_modules);
        return $this->response($res ? 1 : 990);        
    }

    public function salesSlip(Request $request, $id)
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

    
    public function tidCreate(Request $request)
    {
        //0523070000 pg(2) + ym(2) + idx(4)
        $pg_type = sprintf("%02d", $request->pg_type);
        $date = date('ym');

        $cur_month = Carbon::now()->startOfMonth();
        $next_month = $cur_month->copy()->addMonth(1)->startOfMonth()->format('Y-m-d');
        $cur_month = $cur_month->format('Y-m-d');
        $pay_modules = $this->pay_modules
            ->where('created_at', '>=', $cur_month)
            ->where('created_at', '<', $next_month)
            ->get(['tid']);

        $pattern = '/^'.$pg_type.$date.'[0-9]{4}$/';
        $cur_modules = $pay_modules->filter(function($pay_module) use($pattern) {  
            return preg_match($pattern, $pay_module->tid) === 1;
        });
        if($cur_modules->count())
        {
            $idx = $cur_modules->map(function($pay_module) {
                return (int)substr($pay_module->tid, -4);
            })->max()+1;
        }
        else
            $idx = 0;

        $tid = sprintf($pg_type.$date.'%04d', $idx);
        return $this->response(0, ['tid'=>$tid]);    
    }

    public function getNewPayKey($id)
    {
        return $id.Str::random(64 - strlen((string)$id));
    }

    public function payKeyCreate(Request $request)
    {
        $pay_key = $this->getNewPayKey($request->id);
        $res = $this->pay_modules
            ->where('id', $request->id)
            ->update(['pay_key' => $pay_key]);
        return $this->response(0, ['pay_key'=>$pay_key]);    
    }
}
