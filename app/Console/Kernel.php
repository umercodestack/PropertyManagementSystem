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
        $schedule->command('ical:cron')->everyMinute();

        $schedule->command('updatethread:cron')->everyMinute();

        // // $schedule->command('inspire')->hourly();
        // $schedule->command('execute-scheduled-messages')
        //     ->everyMinute();

        // // $schedule->command('notifications:send-reminders')
        // // ->everyMinute();

        // $schedule->command('notifications:send-reminders')
        //     ->everyFiveMinutes();

        // $schedule->command('notifications:send-reminders')
        // ->everyFifteenMinutes();

        // ->everyFiveMinutes();
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
