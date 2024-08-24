<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('get:leagues')->yearlyOn(8, 10);
        $schedule->command('get:teams')->yearlyOn(8, 10, '00:05');
        $schedule->command('get:fixtures')->twiceDaily(1, 13);
        $schedule->command('fixtures:update-status')->everyMinute();   
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
