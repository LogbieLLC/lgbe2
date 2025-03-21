<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\DB;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Run hourly aggregation every hour at 5 minutes past the hour
        $schedule->command('performance:aggregate hourly')
                 ->hourly()
                 ->at('05')
                 ->appendOutputTo(storage_path('logs/performance-hourly.log'));

        // Run daily aggregation every day at 1:15 AM
        $schedule->command('performance:aggregate daily')
                 ->dailyAt('01:15')
                 ->appendOutputTo(storage_path('logs/performance-daily.log'));

        // Run weekly aggregation every Monday at 2:15 AM
        $schedule->command('performance:aggregate weekly')
                 ->weeklyOn(1, '02:15')
                 ->appendOutputTo(storage_path('logs/performance-weekly.log'));

        // Run monthly aggregation on the 1st of every month at 3:15 AM
        $schedule->command('performance:aggregate monthly')
                 ->monthlyOn(1, '03:15')
                 ->appendOutputTo(storage_path('logs/performance-monthly.log'));

        // Clean up old raw metrics data (retention policy)
        $schedule->call(function () {
            $retention = config('performance.retention.raw_metrics', 30);
            DB::table('performance_metrics')
                ->where('created_at', '<', now()->subDays($retention))
                ->delete();
        })->daily()->at('04:15');

        // Clean up old hourly aggregations
        $schedule->call(function () {
            $retention = config('performance.retention.hourly_aggregations', 7);
            DB::table('aggregated_performance_metrics')
                ->where('aggregation_level', 'hourly')
                ->where('created_at', '<', now()->subDays($retention))
                ->delete();
        })->daily()->at('04:30');

        // Clean up old daily aggregations
        $schedule->call(function () {
            $retention = config('performance.retention.daily_aggregations', 90);
            DB::table('aggregated_performance_metrics')
                ->where('aggregation_level', 'daily')
                ->where('created_at', '<', now()->subDays($retention))
                ->delete();
        })->weekly()->mondays()->at('04:45');

        // Clean up old weekly aggregations
        $schedule->call(function () {
            $retention = config('performance.retention.weekly_aggregations', 365);
            DB::table('aggregated_performance_metrics')
                ->where('aggregation_level', 'weekly')
                ->where('created_at', '<', now()->subDays($retention))
                ->delete();
        })->monthly()->at('05:00');
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
