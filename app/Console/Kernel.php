<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

use App\Http\Controllers\Log\DangerTransController;
use App\Http\Controllers\Manager\BatchUpdater\ApplyBookController;

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
        if((int)env('SCHEDULE_ON', 1))
        {
            $schedule->call(function () {
                (new ApplyBookController)->__invoke();
            })->hourly();
            
            $schedule->call(function () {
                (new DangerTransController(new DangerTransaction))->__invoke();
            })->everySixHours();

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
