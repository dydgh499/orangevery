<?php

namespace App\Http\Controllers\Manager\Withdraws;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Ablilty\Ablilty;
use App\Http\Controllers\Ablilty\EditAbleWorkTime;
use App\Http\Controllers\Ablilty\ActivityHistoryInterface;
use App\Http\Controllers\Manager\Salesforce\UnderSalesforce;

use App\Models\Merchandise\PaymentModule;
use App\Models\Withdraws\VirtualAccount;
use App\Enums\HistoryType;
use App\Http\Traits\StoresTrait;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;

use App\Http\Requests\Manager\Withdraws\VirtualAccountRequest;
use App\Http\Requests\Manager\IndexRequest;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class VirtualAccountController extends Controller
{
    use ManagerTrait, ExtendResponseTrait, StoresTrait;
    protected $virtual_accounts;

    public function __construct(VirtualAccount $virtual_accounts)
    {
        $this->virtual_accounts = $virtual_accounts;
        $this->target           = '정산지갑';
    }

    static public function getUserNameCol($request)
    {
        $level  = (int)$request->input('level', 10);
        if(Ablilty::isMerchandise($request))
            return DB::raw('merchandises.mcht_name as user_name');
        if($level === 10)
            return DB::raw('merchandises.mcht_name as user_name');
        else
            return DB::raw('salesforces.sales_name as user_name');
    }

    static public function getCommonQuery($query, $request)
    {
        $search = $request->input('search', '');
        $level  = (int)$request->input('level', 10);
        if(Ablilty::isMerchandise($request) || $level === 10)
        {
            $query  = $query->join('merchandises', 'virtual_accounts.user_id', '=', 'merchandises.id')
                ->where('virtual_accounts.level', 10)
                ->where(function ($query) use ($search) {
                    return $query->where('virtual_accounts.account_name', 'like', "%$search%")
                        ->orWhere('merchandises.mcht_name', 'like', "%$search%");
                });
            $query = globalSalesFilter($query, $request, 'merchandises');
            $query = globalAuthFilter($query, $request, 'merchandises');
        }
        else
        {
            $query  = $query->join('salesforces', 'virtual_accounts.user_id', '=', 'salesforces.id')
                ->where('virtual_accounts.level', '>', 10)
                ->where(function ($query) use ($search) {
                    return $query->where('virtual_accounts.account_name', 'like', "%$search%")
                        ->orWhere('salesforces.sales_name', 'like', "%$search%");
                });
            $sales_filters = UnderSalesforce::getSelectedSalesFilter($request);
            if(count($sales_filters))
            {
                $query = $query->whereIn('salesforces.id', UnderSalesforce::getSalesIds($request));
                $levels = UnderSalesforce::colToLevel($sales_filters);
                foreach($levels as $level)
                {
                    $query = $query->where('salesforces.id', '<=', $level);
                }
            }
        
        }
        return $query->where('virtual_accounts.brand_id', $request->user()->brand_id);
    }

    /**
     * 목록출력
     *
     * 운영자 이상 가능
     *
     * @queryParam search string 검색어(아아디)
     */
    public function index(IndexRequest $request)
    {
        $cols   = ['virtual_accounts.*'];
        $query  = self::getCommonQuery($this->virtual_accounts, $request);
        $cols[] = self::getUserNameCol($request);
        $data = $this->getIndexData($request, $query, 'virtual_accounts.id', $cols, 'virtual_accounts.created_at');
        return $this->response(0, $data);
    }

    /**
     * 추가
     *
     */
    public function store(VirtualAccountRequest $request)
    {
        if(Ablilty::isOperator($request))
        {
            if(EditAbleWorkTime::validate() === false)
                return $this->extendResponse(1500, '지금은 작업할 수 없습니다.');
            else
            {
                $data = $request->data();
                $data['user_id']    = $request->user_id;
                $data['brand_id']   = $request->user()->brand_id;
                $data['created_at'] = date("Y-m-d H:i:s");
                $data['account_code'] = self::generateCode();
                $res = app(ActivityHistoryInterface::class)->add($this->target, $this->virtual_accounts, $data, 'account_code');
                if($res)
                    return $this->response(1, ['id' => $res->id, 'account_code' => $data['account_code']]);
                else
                    return $this->response(990, []);
            }
        }
        else
            return $this->response(951);
    }

    /**
     * 단일조회
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if(Ablilty::isOperator($request))
        {
            $data = $this->virtual_accounts->where('id', $id)->first();
            return $this->response($data ? 0 : 1000, $data);    
        }
        else
            return $this->response(951);
    }

    /**
     * 업데이트
     *
     * @return \Illuminate\Http\Response
     */
    public function update(VirtualAccountRequest $request, $id)
    {
        if(Ablilty::isOperator($request))
        {
            $query  = $this->virtual_accounts->where('id', $id);
            $user   = $query->first();
            if(EditAbleWorkTime::validate() === false)
                return $this->extendResponse(1500, '지금은 작업할 수 없습니다.');
            else
            {
                $data = $request->data();
                $row = app(ActivityHistoryInterface::class)->update($this->target, $query, $data, 'account_code');
                if($row)
                    return $this->response(1, ['id' => $id]);
                else
                    return $this->response(990);

            }
        }
        else
            return $this->response(951);
    }

    /**
     * 단일삭제
     *
     * 운영자 이상 가능
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, int $id)
    {
        if(Ablilty::isOperator($request))
        {
            if(EditAbleWorkTime::validate() === false)
                return $this->extendResponse(1500, '지금은 작업할 수 없습니다.');
            else
            {
                $query = $this->virtual_accounts->where('id', $id);
                $row = app(ActivityHistoryInterface::class)->destory($this->target, $query, 'account_code', '', HistoryType::DELETE, false);
                return $this->response(1, ['id' => $id]);
            }
        }
        else
            return $this->response(951);
    }

    static public function generateCode()
    {
        do {
            $account_code = strtoupper(Str::random(10));
        }
        while(VirtualAccount::where('account_code', $account_code)->exists());
        return $account_code;
    }

    public function all(Request $request)
    {
        $cols = [
            'virtual_accounts.id',
            'virtual_accounts.user_id',
            'virtual_accounts.level',
            'virtual_accounts.account_name',
            'virtual_accounts.account_code',
        ];
        $query  = $this->virtual_accounts
            ->join('merchandises', 'virtual_accounts.user_id', '=', 'merchandises.id')
            ->where('virtual_accounts.level', 10)
            ->where('virtual_accounts.brand_id', $request->user()->brand_id);
        $query = globalAuthFilter($query, $request, 'merchandises');
        $mchts = $query->get($cols);

        $query  = $query->join('salesforces', 'virtual_accounts.user_id', '=', 'salesforces.id')
                ->where('virtual_accounts.level', '>', 10)
                ->where('virtual_accounts.brand_id', $request->user()->brand_id);
        $sales_filters = UnderSalesforce::getSelectedSalesFilter($request);
        if(count($sales_filters))
        {
            $query = $query->whereIn('salesforces.id', UnderSalesforce::getSalesIds($request));
            $levels = UnderSalesforce::colToLevel($sales_filters);
            foreach($levels as $level)
            {
                $query = $query->where('salesforces.id', '<=', $level);
            }
        }
        $sales = $query->get($cols);
        return $this->response(0, ['mchts' => $mchts, 'sales' => $sales]);
    }
    

    static public function syncVirtualAccount()
    {
        $pay_modules = PaymentModule::where('use_realtime_deposit', 1)
            ->whereNotNull('fin_id')
            ->where('is_delete', false)
            ->get();
        foreach($pay_modules as $pay_module)
        {
            DB::transaction(function () use($pay_module) {
                $account_code = self::generateCode();
                $data = [
                    'account_name'  => $account_code,
                    'account_code'  => $account_code,
                    'fin_id'        => $pay_module->fin_id,
                    'user_id'       => $pay_module->mcht_id,
                    'brand_id'      => $pay_module->brand_id,
                    'withdraw_limit_type'       => $pay_module->withdraw_limit_type,
                    'withdraw_business_limit'   => $pay_module->withdraw_business_limit,
                    'withdraw_holiday_limit'    => $pay_module->withdraw_holiday_limit,
                    'level' => 10,
                ];
                if($pay_module->fin_trx_delay === -1)
                {
                    $data['withdraw_type'] = 0;
                    $data['fin_trx_delay'] = 0;
                }
                else
                {
                    $data['withdraw_fee']  = $pay_module->settle_fee;
                    $data['withdraw_type'] = 1;
                    $data['fin_trx_delay'] = $pay_module->fin_trx_delay;
                }
                $res = VirtualAccount::create($data);
                if($res)
                {
                    $pay_module->va_id = $res->id;
                    $pay_module->settle_fee = 0;
                    $pay_module->save();
                }
                echo $pay_module->id." \n";
            });
        }
    }
}
