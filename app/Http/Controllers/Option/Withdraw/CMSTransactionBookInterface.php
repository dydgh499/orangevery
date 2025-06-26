<?php
namespace App\Http\Controllers\Option\Withdraw;

use App\Http\Controllers\Option\Withdraw\CMSTransactionValidate;


use App\Models\Service\CMSTransactionBooks;

use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use Carbon\Carbon;

class CMSTransactionBookInterface extends CMSTransactionValidate
{
    public static function addCancelJobLogs($trx_ids)
    {
        $histories = json_decode(json_encode(
            CMSTransactionBooks::whereIn('trans_seq_num', $trx_ids)
            ->where('is_withdraw', 1)
            ->get()
        ), true);
        if(count($histories))
        {
            $result_code = -5;
            $message     = '사용자가 이체 예약을 취소하였습니다.';
            /*
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
                */
        }
    }
    
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

        foreach ($trx_ids as $trx_id)
        {
            $key_name = 'book-withdraw-' . $trx_id;
            $dest_job_id = Redis::get($key_name);

            if ($dest_job_id)
            {
                $removed_delayed = $findJobAndRemove($delayed_jobs, $dest_job_id, 'delayed');
                $removed_reserved = $findJobAndRemove($reserved_jobs, $dest_job_id, 'reserved');

                if ($removed_delayed || $removed_reserved) {
                    // Job 제거 성공 → DB에도 취소 표시
                    CMSTransactionBooks::where('trans_seq_num', $trx_id)->update([
                        'withdraw_status' => 1,
                        'updated_at' => now()
                    ]);
                    Redis::del($key_name);
                    $goods[] = $trx_id;
                } else {
                    $fails[] = $trx_id;
                }
            }
            else {
                $not_founds[] = $trx_id;
            }
        }

        self::addCancelJobLogs($goods);
        return [$goods, $fails, $not_founds];
    }
}
