<?php

namespace App\Http\Controllers\Log;

use App\Models\Merchandise;
use App\Models\Log\RealtimeSendHistory;

use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Requests\Manager\IndexRequest;

use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * @group Realtime-Send-History API
 *
 * 실시간 이체이력 API 입니다.
 */
class RealtimeSendHistoryController extends Controller
{
    use ManagerTrait, ExtendResponseTrait;
    protected $realtime_send_histories;

    public function __construct(RealtimeSendHistory $realtime_send_histories)
    {
        $this->realtime_send_histories = $realtime_send_histories;
    }
    
    public function getAutoWithdrawParams($merchandises, $pay_modules)
    {
        $params = $merchandises->map(function ($merchandise) use ($pay_modules) {
            $idx = array_search($merchandise->id, array_column($pay_modules, 'mcht_id'));
            if($idx !== false)
            {
                $pay_module = $pay_modules[$idx];
                // 정산금
                $profit = $merchandise->noSettles->sum('mcht_settle_amount');
                // 출금 완료금
                $total_withdraw_amount = $merchandise->collectWithdraws->sum('total_withdraw_amount');       
                // 취소 후 입금
                $cancel_deposit = $merchandise->noSettles->reduce(function($carry, $transaction) {
                    return $carry + $transaction->cancelDeposits->sum('deposit_amount');
                }, 0);
                //정산금 + 취소후 입금 - 출금 완료금
                $withdraw_amount = ($profit + $cancel_deposit - $total_withdraw_amount);
                $withdraw_fee = $merchandise->collect_withdraw_fee;                
                if($withdraw_amount - $withdraw_fee > 0)
                {
                    return [
                        'brand_id' => $merchandise->brand_id,
                        'mcht_id' => $merchandise->id,
                        'withdraw_amount' => $withdraw_amount - $withdraw_fee,
                        'withdraw_fee'    => $withdraw_fee,
                        'fin_id' => $pay_module['fin_id'],
                        'acct_num' => $merchandise->acct_num,
                        'acct_name' => $merchandise->acct_name,
                        'acct_bank_name' => $merchandise->acct_bank_name,
                        'acct_bank_code' => $merchandise->acct_bank_code,
                    ];
                }
            }
            return [];
        })->all();

        logging(['params'=>$params], 'realtime-auto-withdraws');
        return $params;
    }

    /**
     * 1시간 간격으로 실시간 정산금 자동 스케줄링 (모아서 출금)
     */
    public function __invoke()
    {
        request()->merge([
            's_dt' => '2000-01-01',  
            'e_dt' => Carbon::now()->format('Y-m-d'),
        ]);
        $merchandises = Merchandise::join('payment_modules', 'merchandises.id', '=', 'payment_modules.mcht_id')
            ->where('merchandises.use_collect_withdraw', true)
            ->where('merchandises.is_delete', false)
            ->where('payment_modules.use_realtime_deposit', true)
            ->where('payment_modules.is_delete', false)
            ->where('payment_modules.fin_id', '>', 0)
            ->where('payment_modules.fin_trx_delay', -2)
            ->with(['noSettles.cancelDeposits', 'collectWithdraws'])
            ->get([
                'merchandises.brand_id', 'merchandises.id', 'merchandises.collect_withdraw_fee', 
                'merchandises.acct_num', 'merchandises.acct_name', 'merchandises.acct_bank_name', 
                'merchandises.acct_bank_code', 'payment_modules.id as pmod_id', 'payment_modules.fin_id'
            ]);
        $pay_modules = $merchandises->map(function ($merchandise) {
            return [
                'id'      => $merchandise->pmod_id,
                'mcht_id' => $merchandise->id,
                'fin_id'  => $merchandise->fin_id,
            ];
        })->all();
        $merchandises = $merchandises->unique('id');        
        $params = $this->getAutoWithdrawParams($merchandises, $pay_modules);

        foreach($params as $param)
        {
            $res = post(env('NOTI_URL', 'http://localhost:81').'/api/v2/realtimes/collect-deposit', $param);
            if($res['code'] == 201)
                return $this->response($res ? 1 : 990);
        }
    }

    public function commonSelect($request)
    {
        $search = $request->input('search', '');
        $query  = $this->realtime_send_histories
            ->join('transactions', 'realtime_send_histories.trans_id', '=', 'transactions.id')
            ->join('merchandises', 'transactions.mcht_id', '=', 'merchandises.id')
            ->where('transactions.brand_id', $request->user()->brand_id)
            ->where('realtime_send_histories.is_delete', false);

        $query = globalPGFilter($query, $request, 'transactions');
        $query = globalSalesFilter($query, $request, 'transactions');
        $query = globalAuthFilter($query, $request, 'transactions');
        
        if($search != '')
        {
            $query = $query->where(function ($query) use ($search) {
                return $query->where('transactions.appr_num', 'like', "%$search%")
                    ->orWhere('merchandises.mcht_name', 'like', "%$search%")
                    ->orWhere('realtime_send_histories.acct_num', 'like', "%$search%");
            });
        }
        return $query;
    }

    /**
     * 목록출력
     *
     */
    public function index(IndexRequest $request)
    {
        $cols = [
            'merchandises.mcht_name',
            'transactions.appr_num',
            'transactions.trx_id',
            'realtime_send_histories.*',
        ];
        $query = $this->commonSelect($request);
        $data = $this->getIndexData($request, $query, 'realtime_send_histories.id', $cols, 'realtime_send_histories.created_at');
        return $this->response(0, $data);
    }
}
