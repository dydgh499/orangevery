<?php
namespace App\Http\Controllers\Option\Withdraw;

use App\Http\Controllers\Option\Withdraw\CMSTransactionValidate;
use App\Http\Controllers\Utils\FinanceVanUtil;

use App\Models\Withdraws\VirtualAccountHistory;
use App\Models\Service\CMSTransactionBooks;

use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\DB;

use Carbon\Carbon;
use App\Jobs\AuthWithdraws;
use App\Jobs\PaytusRealtimeSettles;
use App\Jobs\Realtime\Realtime;
use App\Jobs\Realtime\ScheduledWithdraw;

class CMSTransactionWithdrawBookInterface extends CMSTransactionValidate
{
    static public function addCancelJobLogs($trx_ids)
    {
        $histories = json_decode(json_encode(
            VirtualAccountHistory::whereIn('trx_id', $trx_ids)
            ->where('trans_type', 1)
            ->get()
        ), true);
        if(count($histories))
        {
            $result_code = -5;
            $message     = '사용자가 이체 예약을 취소하였습니다.';
            foreach($histories as $history)
            {
                self::addWithdraw($history, 0, 6170, $result_code, $message, [
                    'acct_num' => '',
                    'acct_name'=> '',
                    'acct_bank_name' => '',
                    'acct_bank_code' => '',
                    'acct_num' => '',
                ], null);
                self::updateHistory($history['id'], 4, $history['trans_amount']);
            }
        }
    }
    
    // 이체예약 취소
    static public function cancelJobs($trx_ids)
    {
        $findJobAndRemove = function($jobs, $dest_job_id, $queue_type) {
            foreach ($jobs as $_job => $score)
            {
                $job = json_decode($_job, true);
                if(isset($job['id']) && $job['id'] === $dest_job_id)
                {
                    Redis::zrem("queues:realtime:$queue_type", $_job);
                    return true;
                }
            }
            return false;
        };
        $goods = [];
        $fails = [];
        $not_founds = [];
        $delayed_jobs = Redis::zrange("queues:realtime:delayed", 0, -1, ['WITHSCORES' => true]);
        $reserved_jobs = Redis::zrange("queues:realtime:reserved", 0, -1, ['WITHSCORES' => true]);

        for ($i=0; $i < count($trx_ids); $i++)
        {
            $trx_id = $trx_ids[$i];
            $key_name = 'auth-withdraw-'.$trx_id;
            $dest_job_id = Redis::get($key_name);
            if($dest_job_id)
            {
                $r1 = $findJobAndRemove($delayed_jobs, $dest_job_id, 'delayed');
                $r2 = $findJobAndRemove($reserved_jobs, $dest_job_id, 'reserved');

                if ($r1 || $r2)
                    $goods[] = $trx_id;
                else
                    $fails[] = $trx_id;
            }
            else
                $not_founds[] = $trx_id;
        }
        self::addCancelJobLogs($goods);
        return [$goods, $fails, $not_founds];
    }

    // 자동이체 대상
    static public function isAuthWithdrawTransaction($trans, $virtual_account)
    {   // 승인건
        if((int)$trans['is_cancel'] === 0)
        {   // 즉시출금, 가맹점만
            if($virtual_account['withdraw_type'] === 1 && $virtual_account['level'] === 10)
                return true;
        }
        return false;
    }

    // 자동이체 예약 수정중
    static public function bookWithdraw($virtual_account, $history, $user, $delay, $trans_amount)
    {   // 계좌번호, 예금주명, 은행명, 은행코드, 이체모듈 가져오기
        $privacy = FinanceVanUtil::getPrivacy($user);
        $finance = FinanceVanUtil::getFianceVan($virtual_account['fin_id']);
        if($finance)
        {
            $finance = json_decode(json_encode($finance), true);

            $job    = new AuthWithdraws($virtual_account, $history, $finance, $privacy, $trans_amount);
            $job_id = Bus::dispatch($job->onConnection('redis')->onQueue('realtime')->delay($delay));
            
            $diff_secound = Carbon::now()->diffInSeconds($delay);
            if($diff_secound > 0) 
                Redis::set('auth-withdraw-'.$history['trx_id'], $job_id, 'EX', $diff_secound);
            return $job_id;
        }
        else
        {
            throw new Exception('금융 VAN 미존재');
            return null;
        }
    }

    static public function addWithdrawHistory($trans, $virtual_account, $trans_amount, $delay)
    {
        $data = [
            'trans_amount'  => $trans_amount,
            'trans_type'    => 1,
            'trx_id'        => $trans['trx_id'],
            'withdraw_status'=> 0,
            'withdraw_type' => $virtual_account['withdraw_type'],
            'withdraw_fee'  => $virtual_account['withdraw_fee'],
            'withdraw_schedule_time' => $delay
        ];
        $res = self::addHistory($virtual_account, $data);
        if($res->id)
        {
            $data['id'] = $res->id;
            self::updateVirtualAccount($virtual_account);
        }
        else
            $data['id'] = null;
        return $data;
    }
}
