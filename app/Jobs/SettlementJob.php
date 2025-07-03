<?php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Http\Controllers\Manager\Transaction\TransactionAPI;
use App\Http\Controllers\Manager\Transaction\WithdrawAPI;
use App\Models\Pay\Transaction;

class SettlementJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $transactions;

    public function __construct($transactions)
    {
        $this->transactions = $transactions;
    }

    // 4. 결제 시도
    public function batchPay()
    {
        $success = [];
        $fails   = [];
        foreach($this->transactions as $transaction)
        {
            try
            {
                $res = TransactionAPI::billPay([
                    'mid'       => $transaction['mid'],
                    'tid'       => $transaction['tid'],
                    'pmod_id'   => $transaction['pmod_id'],
                    'buyer_name'    => $transaction['buyer_name'],
                    'buyer_phone'   => $transaction['buyer_phone'],
                    'bill_key'      => $transaction['bill_key'],
                    'amount'        => $transaction['amount'],
                    'installment'   => 0,
                ], $transaction['pay_key']);
                if($res['body']['result_cd'] === '0000')
                    $success[] = array_merge($res['body'], $transaction);
                else
                    $fails[] = array_merge(['message' => $res['body']['result_msg']], $transaction);
            }
            catch(\Exception $ex)
            {
                error($transaction, 'settlement-job-batchPay'.$ex->getMessage());
                $fails[] = array_merge(['message' => '내부 처리 에러'], $transaction);
            }
        }
        $this->batchPayUpdate($success, $fails);
        return [$success, $fails];
    }

    // 5. 결제 상태 업데이트
    public function batchPayUpdate($successes, $fails)
    {
        foreach($successes as $success)
        {
            Transaction::where('id', $success['id'])->update([
                'trx_status'    => 1,
                'trx_at'        => $success['trx_at'],
                'ord_num'       => $success['ord_num'],
                'trx_id'        => $success['trx_id'],
                'card_num'      => $success['card_num'],
                'issuer'        => $success['issuer'],
                'acquirer'      => $success['acquirer'],
                'appr_num'      => $success['appr_num'],
                'installment'   => $success['installment'],
            ]);
        }
        foreach($fails as $fail)
        {
            Transaction::where('id', $fail['id'])->update([
                'trx_status'    => 3,
                'message'       => $fail['message'],
            ]);
        }
    }

    // 6. 이체 진행
    public function batchDeposit($pay_success)
    {
        $successes = [];
        $fails   = [];

        foreach($pay_success as $transaction)
        {
            try
            {
                $res = WithdrawAPI::withdraw([
                    'fin_id'            => env("DELIVERY_FIN_ID", null), 
                    'acct_bank_name'    => $transaction['acct_bank_name'], 
                    'acct_num'          => $transaction['acct_num'], 
                    'acct_name'         => $transaction['acct_name'], 
                    'acct_bank_code'    => $transaction['acct_bank_code'],
                    'withdraw_amount'   => $transaction['amount'], 
                ]);
                if($res['body']['result_cd'] === '0000')
                    $successes[] = array_merge($res['body'], $transaction);
                else
                    $fails[] = array_merge(['message' => $res['body']['result_msg']], $transaction);
            }
            catch(\Exception $ex)
            {
                error($transaction, 'settlement-job-batchPay'.$ex->getMessage());
                $fails[] = array_merge(['message' => '내부 처리 에러'], $transaction);
            }
        }
        $this->batchDepositUpdate($successes, $fails);
        return [$successes, $fails];
    }

    // 7. 이체 상태 업데이트
    public function batchDepositUpdate($successes, $fails)
    {
        foreach($successes as $success)
        {
            Transaction::where('id', $success['id'])->update([
                'trx_status'    => 5,
                'cms_id'        => $success['cms_id'],
            ]);
        }
        foreach($fails as $fail)
        {
            Transaction::where('id', $fail['id'])->update([
                'trx_status'    => 7,
                'message'       => $fail['message'],
            ]);
        }
    }

    public function handle()
    {
        logging($this->transactions, 'settlement-job-start');
        [$pay_success, $pay_fails]          = $this->batchPay();
        [$deposit_success, $deposit_fails]  = $this->batchDeposit($pay_success);
    }
}
