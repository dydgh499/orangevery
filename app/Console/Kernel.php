<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

use App\Http\Controllers\Log\FeeChangeHistoryController;
use App\Http\Controllers\Log\DangerTransController;
use App\Http\Controllers\Log\RealtimeSendHistoryController;
use App\Http\Controllers\Log\DifferenceSettlementHistoryController;

use App\Models\Log\DifferenceSettlementHistory;
use App\Models\Log\MchtFeeChangeHistory;
use App\Models\Log\RealtimeSendHistory;
use App\Models\Log\SfFeeChangeHistory;
use App\Models\Log\DangerTransaction;


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
        $schedule->command('sanctum:prune-expired --hours=35')->daily();
        $schedule->call(new FeeChangeHistoryController(new MchtFeeChangeHistory, new SfFeeChangeHistory))->daily();
        $schedule->call(new RealtimeSendHistoryController(new RealtimeSendHistory))->hourly();
        $schedule->call(new DangerTransController(new DangerTransaction))->everySixHours();
        
        $schedule->call(function () {
            (new DifferenceSettlementHistoryController(new DifferenceSettlementHistory))->differenceSettleRequest();
        })->daily();
        $schedule->call(function () {
            (new DifferenceSettlementHistoryController(new DifferenceSettlementHistory))->differenceSettleResponse();
        })->dailyAt("04:30");

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
