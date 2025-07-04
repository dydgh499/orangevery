<?php

namespace App\Http\Controllers\Manager\BatchUpdater;

use App\Http\Controllers\Manager\BatchUpdater\BatchUpdateController;
use App\Http\Controllers\Manager\BatchUpdater\BatchUpdateBankAccountController;
use App\Http\Requests\Manager\BulkRegister\BulkTransactionRequest;
use App\Http\Controllers\Ablilty\ActivityHistoryInterface;
use App\Models\Pay\BillKey;

use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Traits\StoresTrait;
use App\Models\Pay\Transaction;

use Illuminate\Support\Facades\Bus;
use App\Jobs\SettlementJob;

/**
 * @group Withdraw-Batch-Book-Updater API
 *
 * 정산하기 group 입니다.
 */
class BatchTransactionController extends BatchUpdateController
{
    use ManagerTrait, ExtendResponseTrait, StoresTrait;
    protected $transactions;

    public function __construct(Transaction $transactions)
    {
        $this->transactions = $transactions;
        $this->target       = '정산예약';
    }
    
    public function getTransactionParams($data, $pay_module)
    {
        return [
            'bill_id'   => $data['bill_id'],
            'pmod_id'   => $pay_module['id'],
            'pg_id'     => $pay_module['pg_id'],
            'ps_id'     => $pay_module['ps_id'],
            'ps_fee'    => $pay_module['ps_fee'],
            'trx_at'    => date('Y-m-d H:i:s'),
            'module_type' => $pay_module['module_type'],
            'mid'       => $pay_module['mid'],
            'tid'       => $pay_module['tid'],
            'is_cancel' => 0,
            'amount'    => $data['amount'],
            'trx_status'=> 0,
        ];
    }

    public function addTransactionObjects($request, $datas)
    {
        $current = date('Y-m-d H:i:s');
        $trans = $datas->map(function ($data) use($current, $request) {
            $data['brand_id']   = $request->user()->brand_id;
            $data['oper_id']    = $request->user()->id;
            $data['created_at'] = $current;
            $data['updated_at'] = $current;
            return $data;
        })->toArray();
        return app(ActivityHistoryInterface::class)->batchAdd($this->target, $this->transactions, $trans, 'mid', $current, $request->user()->brand_id);
    }

    public function getNewTransactions($request)
    {
        $transactions   = [];
        $keys           = [];
        $datas = $request->data();
        $bill_ids = $datas->pluck('bill_id')->unique()->all();
        $query = Billkey::join('payment_modules', 'bill_keys.pmod_id', '=', 'payment_modules.id')
            ->join('payment_sections', 'payment_modules.ps_id', '=', 'payment_sections.id')
            ->whereIn('bill_keys.id', $bill_ids);
        $payment_modules = brandFilter($query, $request, 'payment_modules')->get([
            'payment_modules.*', 
            'payment_sections.trx_fee as ps_fee',
            'bill_keys.id as bill_id', 
            'bill_keys.bill_key',
        ]);
        if($payment_modules)
        {
            $payment_modules = $payment_modules->map(function ($item) {
                return $item->makeVisible(['bill_key'])->toArray();
            })->all();
            for($i=0; $i<count($datas); $i++)
            {
                $idx = array_search($datas[$i]['bill_id'], array_column($payment_modules, 'bill_id'));
                if($idx !== false)
                {
                    $transactions[] = $this->getTransactionParams($datas[$i], $payment_modules[$idx]);
                    $keys[$datas[$i]['bill_id']] = [
                        'bill_key'  => $payment_modules[$idx]['bill_key'],
                        'pay_key'   => $payment_modules[$idx]['api_key'],
                    ];
                }
                else
                    return [false, ($i + 1)."번째 빌키에 매칭된 결제모듈이 존재하지 않습니다.", null, null];
            }
            return [true, '', $transactions, $keys];
        }
        else
            return [false, '결제모듈이 존재하지 않습니다.', null, null];
    }

    public function bookSettlement($request, $ids, $datas, $keys)
    {
        $accounts = $request->data();
        for($i=0; $i<count($datas); $i++)
        {
            $key = $keys[$datas[$i]['bill_id']];
            if($key)
            {
                $datas[$i]['bill_key'] = $key['bill_key'];
                $datas[$i]['pay_key'] = $key['pay_key'];
            }
            else
            {
                error($datas[$i], 'KEY 정보 미존재');
                $datas[$i]['bill_key']  = '';
                $datas[$i]['pay_key']   = '';
            }
            $datas[$i]['id'] = $ids[$i];
            $datas[$i]['buyer_name']    = $request->user()->nick_name;
            $datas[$i]['buyer_phone']   = $request->user()->buyer_phone;
            $datas[$i] = array_merge($datas[$i], app(BatchUpdateBankAccountController::class)->getBankAccountParams($accounts[$i]));            
        }
        /** 하기 내용 진행
         * 4. 결제 시도
         * 5. 결제 상태 업데이트
         * 6. 이체 진행
         * 7. 이체 상태 업데이트
         */
        $job = new SettlementJob($datas);
        return Bus::dispatch($job->onConnection('redis')->onQueue('realtime')->delay(0));
    }

    /**
     * 대량정산
     *
     * 운영자 이상 가능
     */
    public function register(BulkTransactionRequest $request)
    {
        # 1. 계좌 조회
        [$news, $error] = app(BatchUpdateBankAccountController::class)->getNewAccounts($request);
        $ids            = app(BatchUpdateBankAccountController::class)->addBankAccountObjects($request, collect($news));
        if($error)
            return $this->extendResponse(9999, $error['body']['message']);
        else
        {
            [$result, $message, $datas, $keys] = $this->getNewTransactions($request);
            if($result)
            {
                // 2. 거래건 등록
                $ids = $this->addTransactionObjects($request, collect($datas));
                // 3번부터 예약
                $job_id = $this->bookSettlement($request, $ids, $datas, $keys);
                if($job_id)
                    return $this->extendResponse(1, '정산예약에 성공하였습니다. 정산현황 페이지를 확인해주세요.');
                else
                    return $this->extendResponse(9999, '결제/이체 예약에 실패하였습니다.');
            }
            else
                return $this->extendResponse(9999, $message);
        }
    }
}
