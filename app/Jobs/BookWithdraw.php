<?php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Jobs\Realtime\RealtimeWrapper;

use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class BookWithdraw implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $finance_van;
    protected $privacy;
    protected $withdraw_amount;
    protected $note;
    protected $withdraw_book_time;

    public function __construct($finance_van, $privacy, $withdraw_amount, $note, $withdraw_book_time)
    {
        $this->finance_van = $finance_van;
        $this->privacy = $privacy;
        $this->withdraw_amount = $withdraw_amount;
        $this->note = $note;
        $this->withdraw_book_time = $withdraw_book_time;
    }

    public function handle()
    {
        Log::info('[BookWithdraw] 이체 실행', [
            '실행시각' => Carbon::now()->toDateTimeString(),
            '예약금액' => $this->withdraw_amount,
            '메모' => $this->note,
        ]);
        $rt = new RealtimeWrapper($this->finance_van, $this->privacy, 4, $this->withdraw_book_time);
        if (!$rt->service) {
            Log::error("OperateWithdrawJob 실패: 서비스 없음");
            return;
        }

        $json = $rt->operateWithdraw($this->withdraw_amount, $this->note);
        Log::info("BookWithdraw 응답", $json);
    }
}
