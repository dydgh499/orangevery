<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

use App\Http\Controllers\Manager\Service\HolidayController;
use App\Http\Controllers\Log\FeeChangeHistoryController;
use App\Http\Controllers\Log\DangerTransController;
use App\Http\Controllers\Log\RealtimeSendHistoryController;
use App\Http\Controllers\Log\DifferenceSettlementHistoryController;
use App\Http\Controllers\Manager\BatchUpdater\ApplyBookController;

use App\Models\Log\DifferenceSettlementHistory;
use App\Models\Log\MchtFeeChangeHistory;
use App\Models\Log\RealtimeSendHistory;
use App\Models\Log\SfFeeChangeHistory;
use App\Models\Log\DangerTransaction;
use App\Models\Service\Holiday;


class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        if((int)env('SCHEDULE_ON', 1))
        {
            $schedule->call(function () {
                (new ApplyBookController)->__invoke();
            })->hourly();
            
            $schedule->call(function () {
                (new DangerTransController(new DangerTransaction))->__invoke();
            })->everySixHours();

            $schedule->call(function () {
                (new FeeChangeHistoryController(new MchtFeeChangeHistory, new SfFeeChangeHistory))->__invoke();
            })->daily();

            // 차액정산 처리
            $schedule->call(function () {
                (new DifferenceSettlementHistoryController(new DifferenceSettlementHistory))->differenceSettleRequest();
            })->dailyAt("00:30");

            $schedule->call(function () {
                (new DifferenceSettlementHistoryController(new DifferenceSettlementHistory))->differenceSettleResponse();
            })->dailyAt("09:00");

            // 공휴일 업데이트
            $schedule->call(function () {
                (new HolidayController(new Holiday))->updateNextHolidaysAllBrands();
            })->yearlyOn(12, 1, '00:00');

            $schedule->command('sanctum:prune-expired --hours=35')->daily();
        }
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}
