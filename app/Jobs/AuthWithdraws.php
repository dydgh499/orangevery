<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use Illuminate\Queue\Middleware\WithoutOverlapping;
use App\Jobs\Realtime\RealtimeWrapper;

class AuthWithdraws implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $maxExceptions   = 1;
    public $tries           = 3;
    public $virtual_account;
    public $history;
    public $privacy;
    public $finance_van;
    public $trans_amount;

    public function __construct($virtual_account, $history, $finance_van, $privacy, $trans_amount)
    {
        $this->virtual_account   = $virtual_account;
        $this->history          = $history;
        $this->finance_van      = $finance_van;
        $this->privacy          = $privacy;
        $this->trans_amount     = $trans_amount;

        if($virtual_account['brand_id'] === 30)
        {
            $this->tries = 24;
        }
    }

    public function failed($ex)
    {
        error([
            'history_id'        => $this->history['id'],
            'virtual_account'    => $this->virtual_account,
        ], "auth-withdraw-fail(".$this->attempts()."):".$ex->getMessage());
    }

    public function middleware()
    {
        return [(new WithoutOverlapping($this->history['id']))->releaseAfter(60)];
    }

    public function handle(): void
    {
        logging([
            'history_id'        => $this->history['id'],
            'virtual_account'    => $this->virtual_account,
        ], "auth-withdraw-start");

        $rt = new RealtimeWrapper($this->finance_van, $this->privacy, 0);
        if($rt->service)
        {
            $res = $rt->virtualAccountWithdraw($this->virtual_account, $this->history, $this->trans_amount);

            if($res['RESP_CD'] !== "0000" && $res['RESP_CD'] !== -1 && $res['RESP_CD'] !== -2 && $res['RESP_CD'] !== -3 && $res['RESP_CD'] !== -4)
            {
                if($this->virtual_account['brand_id'] === 30)
                    $this->release(3600);
            }
        }
        else
            logging(['message' => 'unknwon service'], "auth-withdraw-fail");
    }
}
