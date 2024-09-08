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
        $schedule->command('get:teams')->yearlyOn(8, 10, '00:01');
        $schedule->command('get:standings')->twiceDaily(0, 12, 2);
        $schedule->command('get:results')->twiceDaily(0, 12, 3);
        $schedule->command('calculate:points')->twiceDaily(0, 12, 4);
        $schedule->command('get:fixtures')->twiceDaily(0, 12, 5);
        $schedule->command('fixtures:update-status')->everyMinute();
        $schedule->command('auth:clear-resets')->everyFifteenMinutes();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
