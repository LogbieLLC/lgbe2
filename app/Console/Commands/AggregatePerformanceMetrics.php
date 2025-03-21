<?php

namespace App\Console\Commands;

use App\Services\PerformanceMetricsService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AggregatePerformanceMetrics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'performance:aggregate {type=all : The type of aggregation to run (hourly, daily, weekly, monthly, all)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Aggregate performance metrics data';

    /**
     * Execute the console command.
     */
    public function handle(PerformanceMetricsService $metricsService)
    {
        $type = $this->argument('type');

        $this->info("Starting performance metrics aggregation: {$type}");
        Log::info("Starting performance metrics aggregation: {$type}");

        $startTime = microtime(true);

        switch ($type) {
            case 'hourly':
                $this->aggregateHourly($metricsService);
                break;

            case 'daily':
                $this->aggregateDaily($metricsService);
                break;

            case 'weekly':
                $this->aggregateWeekly($metricsService);
                break;

            case 'monthly':
                $this->aggregateMonthly($metricsService);
                break;

            case 'all':
                $this->aggregateHourly($metricsService);
                $this->aggregateDaily($metricsService);
                $this->aggregateWeekly($metricsService);
                $this->aggregateMonthly($metricsService);
                break;

            default:
                $this->error("Unknown aggregation type: {$type}");
                return 1;
        }

        $duration = round(microtime(true) - $startTime, 2);

        $this->info("Completed performance metrics aggregation in {$duration} seconds");
        Log::info("Completed performance metrics aggregation in {$duration} seconds");

        return 0;
    }

    /**
     * Aggregate hourly metrics
     */
    private function aggregateHourly(PerformanceMetricsService $metricsService)
    {
        $this->info('Aggregating hourly metrics...');

        // Aggregate the previous hour
        $previousHour = Carbon::now()->subHour()->startOfHour();
        $count = $metricsService->aggregateHourlyMetrics($previousHour);

        $this->info("Created {$count} hourly aggregations for {$previousHour->format('Y-m-d H:i')}");
    }

    /**
     * Aggregate daily metrics
     */
    private function aggregateDaily(PerformanceMetricsService $metricsService)
    {
        $this->info('Aggregating daily metrics...');

        // Aggregate the previous day
        $previousDay = Carbon::now()->subDay()->startOfDay();
        $count = $metricsService->aggregateDailyMetrics($previousDay);

        $this->info("Created {$count} daily aggregations for {$previousDay->format('Y-m-d')}");
    }

    /**
     * Aggregate weekly metrics
     */
    private function aggregateWeekly(PerformanceMetricsService $metricsService)
    {
        $this->info('Weekly aggregation not implemented yet');
        // This would be implemented similar to daily aggregation but using weekly data
    }

    /**
     * Aggregate monthly metrics
     */
    private function aggregateMonthly(PerformanceMetricsService $metricsService)
    {
        $this->info('Monthly aggregation not implemented yet');
        // This would be implemented similar to daily aggregation but using monthly data
    }
}
