<?php

namespace App\Http\Controllers\Manager\Settle;

use App\Models\CancelDeposit;
use App\Models\Log\RealtimeSendHistory;
use App\Models\Transaction;
use App\Models\Merchandise;
use App\Models\Merchandise\PaymentModule;
use App\Models\CollectWithdraw;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;

use App\Http\Requests\Manager\Settle\CollectWithdrawRequest;
use App\Http\Requests\Manager\Settle\CollectWithdrawRequestV2;

use App\Http\Requests\Manager\IndexRequest;

use App\Http\Controllers\Utils\Comm;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * @group Collect Withdraw API
 *
 * 모아서 출금 API 입니다.
 */
class CollectWithdrawController extends Controller
{
    use ManagerTrait, ExtendResponseTrait;
    protected $collect_withdraws;

    public function __construct(CollectWithdraw $collect_withdraws)
    {
        $this->collect_withdraws = $collect_withdraws;
        $this->base_noti_url = env('NOTI_URL', 'http://localhost:81').'/api/v2/realtimes';

    }

    public function getPayModules($data)
    {
        $pmod_ids = [];
        foreach($data['content'] as $content)
        {
            $pmod_ids = array_merge($pmod_ids, $content->transactions->pluck('pmod_id')->all());
        }
        return PaymentModule::whereIn('id', array_unique($pmod_ids))->get(['id', 'fin_trx_delay', 'use_realtime_deposit'])->toArray();
    }

    public function setOutputData($data)
    {
        $isRealtimeDeposit = function($pay_modules, $idx) {
            if($idx !== false)
            {   // 즉시출금의 경우, 정산금에 포함하지 않는다.
                if($pay_modules[$idx]['use_realtime_deposit'] && $pay_modules[$idx]['fin_trx_delay'] > -1)
                    return true;
                else
                    return false;
            }
            else
                return true;
        };

        $pay_modules = $this->getPayModules($data);
        $module_ids = array_column($pay_modules, 'id');
        
        foreach($data['content'] as &$content)
        {
            $content->total_amount = 0;
            $content->settle_amount = 0;
            $content->total_withdraw_amount = $content->collectWithdraws->sum('total_withdraw_amount');

            $transactions = json_decode(json_encode($content->transactions), true);
            $not_realtime_transactions = [];
            
            for ($i=0; $i < count($transactions); $i++) 
            { 
                $idx = array_search($transactions[$i]['pmod_id'], $module_ids);
                if($isRealtimeDeposit($pay_modules, $idx) === false)
                    $not_realtime_transactions[] = $transactions[$i];
            }
            for ($i=0; $i < count($not_realtime_transactions) ; $i++) 
            {
                $content->total_amount += $not_realtime_transactions[$i]['amount'];
                $content->settle_amount += $not_realtime_transactions[$i]['mcht_settle_amount'];
            }

            $content->cancel_deposit = $content->transactions->reduce(function($carry, $transaction) {
                return $carry + $transaction->cancelDeposits->sum('deposit_amount');
            }, 0);
            $content->withdraw_able_amount = ( $content->settle_amount + $content->cancel_deposit - $content->total_withdraw_amount) - $content->collect_withdraw_fee;
            $content->makeHidden(['collectWithdraws', 'transactions', 'resident_num_front', 'resident_num_back']);
        }
        return $data;
    }

    public function commonSelect(Request $request)
    {
        $search     = $request->input('search', '');
        $page      = $request->input('page');
        $page_size = $request->input('page_size');
        $sp = ($page - 1) * $page_size;

        $query = CollectWithdraw::join('merchandises', 'collect_withdraws.mcht_id', 'merchandises.id')
            ->where('collect_withdraws.brand_id', $request->user()->brand_id)
            ->where('merchandises.brand_id', $request->user()->brand_id)
            ->where('mcht_name', 'like', "%$search%")
            ->where('is_delete', false);
        $query = globalAuthFilter($query, $request, 'merchandises');
        $query = globalSalesFilter($query, $request, 'merchandises');

        $count = $query->distinct()->pluck('mcht_id')->count();
        $mcht_ids = $query
            ->distinct()
            ->offset($sp)
            ->limit($page_size)
            ->pluck('mcht_id')
            ->all();
        request()->merge([
            's_dt' => '2000-01-01',
            'e_dt' => Carbon::now()->format('Y-m-d'),
        ]);
        $mchts = Merchandise::whereIn('id', $mcht_ids)
            ->with(['collectWithdraws', 'transactions.cancelDeposits'])
            ->get([
                'id',
                'collect_withdraw_fee',
                'mcht_name',
            ]);
        return [$mchts, $count];
    }

    /**
     * 목록출력
     *
     * 가맹점 이상 가능
     *
     * @queryParam search string 검색어(제목)
     */
    public function index(IndexRequest $request)
    {
        [$mchts, $count] = $this->commonSelect($request);
        $data = [
            'page'      => $request->page, 
            'page_size' => $request->page_size,
            'total'     => $count,
            'content'   => $mchts,
        ];
        $data = $this->setOutputData($data);
        return $this->response(0, $data);
    }
    
    /**
     * 출금가능금액이 -1000원 이하인 가맹점들 리턴
     *
     * 운영자 이상 가능
     */
    public function danger(Request $request)
    {
        [$mchts, $count] = $this->commonSelect($request);        
        $data = $this->setOutputData(['content' => $mchts]);
        $danger_mchts = [];
        foreach($data['content'] as $content)
        {
            if($content->withdraw_able_amount < -1000)
                $danger_mchts[] = $content;
        }
        return $this->response(0, ['data'=>$danger_mchts, 'count'=>$count]);
    }

    /**
     * 출금요청
     *
     * 가맹점만 가능
     */
    public function collectDeposit(CollectWithdrawRequest $request)
    {
        $pay_modules = PaymentModule::where('mcht_id', $request->user()->id)
            ->where('is_delete', false)
            ->get(['fin_id', 'use_realtime_deposit']);

        $fin_module = $pay_modules->first(function ($pay_module) {
            return $pay_module->fin_id > 0 && $pay_module->use_realtime_deposit;
        });
        $fin_id = $fin_module ? $fin_module->fin_id : 0;
        if($fin_id)
        {
            $params = [
                'brand_id'          => $request->user()->brand_id,
                'mcht_id'           => $request->user()->id,
                'withdraw_amount'   => $request->withdraw_amount,
                'withdraw_fee'      => $request->user()->collect_withdraw_fee,
                'fin_id' => $fin_id,
            ];
            $res = Comm::post($this->base_noti_url.'/collect-deposit', $params);
            return $this->apiResponse($res['body']['result_cd'], $res['body']['result_msg']);
        }
        else
            return $this->extendResponse(1000, "활성화된 실시간 모듈을 찾을 수 없습니다.");
    }

    /**
     * 출금요청
     *
     * 가맹점만 가능
     */
    public function collectDepositV2(CollectWithdrawRequestV2 $request)
    {
        $pay_modules = PaymentModule::where('mcht_id', $request->user()->id)
            ->where('is_delete', false)
            ->get(['fin_id', 'use_realtime_deposit']);

        $fin_module = $pay_modules->first(function ($pay_module) {
            return $pay_module->fin_id > 0 && $pay_module->use_realtime_deposit;
        });
        $fin_id = $fin_module ? $fin_module->fin_id : 0;
        if($fin_id)
        {
            $params = [
                'brand_id'  => $request->user()->brand_id,
                'mcht_id'   => $request->user()->id,
                'withdraw_amount' => $request->withdraw_amount,
                'withdraw_fee'    => $request->user()->collect_withdraw_fee,
                'fin_id'    => $fin_id,
                'acct_num'  => $request->acct_num,
                'acct_name' => $request->acct_name,
                'acct_bank_code' => $request->acct_bank_code,
            ];
            $res = Comm::post($this->base_noti_url.'/virtual-deposit', $params);
            return $this->apiResponse($res['body']['result_cd'], $res['body']['result_msg']);
        }
        else
            return $this->extendResponse(1000, "활성화된 실시간 모듈을 찾을 수 없습니다.");
    }
    
    public function _withdrawAbleAmount($request, $mcht_id)
    {
        request()->merge([
            's_dt' => '2000-01-01',  
            'e_dt' => Carbon::now()->format('Y-m-d'),
        ]);
        $merchandise = Merchandise::where('id', $mcht_id)
            ->with(['transactions.cancelDeposits', 'collectWithdraws'])
            ->first(['id', 'collect_withdraw_fee']);
        $pay_modules = PaymentModule::whereIn('id', $merchandise->transactions->pluck('pmod_id')->all())
            ->get(['id', 'fin_trx_delay', 'use_realtime_deposit']);

        $total_withdraw_amount  = $merchandise->collectWithdraws->sum('total_withdraw_amount'); // 출금액        
        $cancel_deposit = $merchandise->transactions->reduce(function($carry, $transaction) {
            return $carry + $transaction->cancelDeposits->sum('deposit_amount');
        }, 0);  // 취소 후 입금
        $profit = $merchandise->transactions->reduce(function($carry, $transaction) use($pay_modules) {
            $pay_module = $pay_modules->firstWhere('id', $transaction->pmod_id);
            if($pay_module)
            {   // 즉시출금의 경우, 정산금에 포함하지 않는다.
                if($pay_module->use_realtime_deposit && $pay_module->fin_trx_delay > -1)
                    return $carry;
                else
                    return $carry + $transaction->mcht_settle_amount;
            }
            else
                return $carry;
        }, 0);  // 정산금

        return [
            'profit' => ($profit + $cancel_deposit - $total_withdraw_amount),
            'withdraw_fee' =>  (int)$merchandise->collect_withdraw_fee,
        ];
    }

    /**
     * 출금가능 잔액
     *
     * @return \Illuminate\Http\Response
     */
    public function withdrawAbleAmount(Request $request)
    {
        $data = $this->_withdrawAbleAmount($request, $request->user()->id);
        return $this->response(0, $data);        
    }
}
