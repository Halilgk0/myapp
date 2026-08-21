<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule): void
    {
        // Check device online/offline status every minute
        $schedule->command('devices:check-statuses --minutes=5')
                 ->everyMinute()
                 ->withoutOverlapping()
                 ->runInBackground()
                 ->onOneServer()
                 ->appendOutputTo(storage_path('logs/device-status-check.log'));
                 
        // Legacy device status check (remove if not needed)
        $schedule->command('devices:check-status --minutes=5')
                 ->everyMinute()
                 ->withoutOverlapping()
                 ->runInBackground()
                 ->onOneServer()
                 ->appendOutputTo(storage_path('logs/device-status.log'));
                 
        // Clean up old device locations daily at 1 AM
        $schedule->command('locations:cleanup --days=30')
                 ->dailyAt('01:00')
                 ->onOneServer()
                 ->appendOutputTo(storage_path('logs/location-cleanup.log'));
                 
        // Prune old device locations daily at 2 AM
        $schedule->command('model:prune', [
            '--model' => [
                \App\Models\DeviceLocation::class,
            ],
            '--hours' => 720, // 30 days
        ])->dailyAt('02:00');
        
        // Auto-approve street agency tickets every 10 minutes
        $schedule->command('tickets:auto-approve-street-agency')
                 ->everyTenMinutes()
                 ->withoutOverlapping()
                 ->runInBackground()
                 ->onOneServer()
                 ->appendOutputTo(storage_path('logs/auto-approve-tickets.log'));

        // Tarihi geçen biletleri otomatik gelir kaydı yap
        $schedule->command('tickets:account-expired')
                 ->hourly()
                 ->withoutOverlapping()
                 ->runInBackground()
                 ->onOneServer()
                 ->appendOutputTo(storage_path('logs/account-expired-tickets.log'));

        // Şoför maaşlarını aylık otomatik öde (günlük kontrol)
        $schedule->command('drivers:pay-salaries')
                 ->dailyAt('03:05')
                 ->withoutOverlapping()
                 ->runInBackground()
                 ->onOneServer()
                 ->appendOutputTo(storage_path('logs/pay-driver-salaries.log'));
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
