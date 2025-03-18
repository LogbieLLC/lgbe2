<?php

namespace App\Services;

use App\Models\AggregatedPerformanceMetric;
use App\Models\PerformanceMetric;
use App\Models\PerformanceThreshold;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PerformanceMetricsService
{
    /**
     * Get a summary of core web vitals for a specific time period
     */
    public function getCoreVitalSummary(string $metricName, int $days = 7, string $urlPath = null)
    {
        $metricName = strtolower($metricName);
        $startDate = now()->subDays($days)->startOfDay();
        
        // Get the latest aggregation for this metric
        $aggregation = AggregatedPerformanceMetric::where('metric_name', $metricName)
            ->where('date', '>=', $startDate->toDateString())
            ->when($urlPath, function ($query) use ($urlPath) {
                return $query->where('url_path', $urlPath);
            }, function ($query) {
                return $query->whereNull('url_path'); // Site-wide
            })
            ->where('aggregation_level', 'daily')
            ->whereNull('dimension') // No dimension breakdown
            ->orderBy('date', 'desc')
            ->first();
            
        if (!$aggregation) {
            // If no aggregation exists, calculate on the fly
            return $this->calculateMetricSummary($metricName, $days, $urlPath);
        }
        
        // Get previous period for comparison
        $previousPeriod = AggregatedPerformanceMetric::where('metric_name', $metricName)
            ->where('date', '<', $startDate->toDateString())
            ->when($urlPath, function ($query) use ($urlPath) {
                return $query->where('url_path', $urlPath);
            }, function ($query) {
                return $query->whereNull('url_path'); // Site-wide
            })
            ->where('aggregation_level', 'daily')
            ->whereNull('dimension')
            ->orderBy('date', 'desc')
            ->first();
            
        $percentChange = null;
        if ($previousPeriod && $previousPeriod->p75_value > 0) {
            $percentChange = (($aggregation->p75_value - $previousPeriod->p75_value) / $previousPeriod->p75_value) * 100;
        }
        
        // Get threshold for this metric
        $threshold = PerformanceThreshold::where('metric_name', $metricName)
            ->whereNull('url_pattern')
            ->whereNull('device_type')
            ->first();
            
        $status = 'unknown';
        if ($threshold) {
            $status = $threshold->getStatus($aggregation->p75_value);
        }
        
        return [
            'name' => $metricName,
            'value' => $aggregation->p75_value,
            'status' => $status,
            'change' => $percentChange ? round($percentChange, 1) . '%' : 'N/A',
            'sample_size' => $aggregation->sample_size,
            'p75' => $aggregation->p75_value,
            'p95' => $aggregation->p95_value,
            'avg' => $aggregation->avg_value,
        ];
    }
    
    /**
     * Calculate metric summary on the fly from raw data
     */
    private function calculateMetricSummary(string $metricName, int $days = 7, string $urlPath = null)
    {
        $startDate = now()->subDays($days)->startOfDay();
        
        $query = PerformanceMetric::where('created_at', '>=', $startDate)
            ->whereNotNull($metricName);
            
        if ($urlPath) {
            $query->where('url_path', $urlPath);
        }
        
        $metrics = $query->get()->pluck($metricName)->sort()->values();
        
        if ($metrics->isEmpty()) {
            return [
                'name' => $metricName,
                'value' => null,
                'status' => 'unknown',
                'change' => 'N/A',
                'sample_size' => 0,
                'p75' => null,
                'p95' => null,
                'avg' => null,
            ];
        }
        
        $p75Value = $this->percentile($metrics, 75);
        $p95Value = $this->percentile($metrics, 95);
        $avgValue = $metrics->avg();
        
        // Get threshold for this metric
        $threshold = PerformanceThreshold::where('metric_name', $metricName)
            ->whereNull('url_pattern')
            ->whereNull('device_type')
            ->first();
            
        $status = 'unknown';
        if ($threshold) {
            $status = $threshold->getStatus($p75Value);
        }
        
        return [
            'name' => $metricName,
            'value' => $p75Value,
            'status' => $status,
            'change' => 'N/A', // No previous data for comparison
            'sample_size' => $metrics->count(),
            'p75' => $p75Value,
            'p95' => $p95Value,
            'avg' => $avgValue,
        ];
    }
    
    /**
     * Get metric trend data for a specific time period
     */
    public function getMetricTrend(string $metricName, int $days = 30, string $urlPath = null, string $dimension = null)
    {
        $metricName = strtolower($metricName);
        $startDate = now()->subDays($days)->startOfDay();
        $endDate = now()->endOfDay();
        
        // Determine appropriate aggregation level based on days
        $aggregationLevel = 'daily';
        if ($days <= 2) {
            $aggregationLevel = 'hourly';
        } elseif ($days > 90) {
            $aggregationLevel = 'weekly';
        }
        
        $query = AggregatedPerformanceMetric::where('metric_name', $metricName)
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->where('aggregation_level', $aggregationLevel);
            
        if ($urlPath) {
            $query->where('url_path', $urlPath);
        } else {
            $query->whereNull('url_path'); // Site-wide
        }
        
        if ($dimension) {
            $query->where('dimension', $dimension);
            
            // Group by dimension value
            $results = $query->get()->groupBy('dimension_value');
            
            $trendData = [];
            foreach ($results as $dimensionValue => $metrics) {
                $trendData[$dimensionValue] = $metrics->sortBy('date')->values()->map(function ($metric) {
                    return [
                        'date' => $metric->date->format('Y-m-d'),
                        'hour' => $metric->hour,
                        'value' => $metric->p75_value,
                        'sample_size' => $metric->sample_size,
                    ];
                });
            }
            
            return $trendData;
        }
        
        // No dimension - return simple time series
        return $query->orderBy('date')
            ->orderBy('hour')
            ->get()
            ->map(function ($metric) {
                return [
                    'date' => $metric->date->format('Y-m-d'),
                    'hour' => $metric->hour,
                    'value' => $metric->p75_value,
                    'sample_size' => $metric->sample_size,
                ];
            });
    }
    
    /**
     * Get device type breakdown for a specific metric
     */
    public function getDeviceTypeBreakdown(string $metricName, int $days = 7, string $urlPath = null)
    {
        $metricName = strtolower($metricName);
        $startDate = now()->subDays($days)->startOfDay();
        
        $query = AggregatedPerformanceMetric::where('metric_name', $metricName)
            ->where('date', '>=', $startDate->toDateString())
            ->where('dimension', 'device_type')
            ->where('aggregation_level', 'daily');
            
        if ($urlPath) {
            $query->where('url_path', $urlPath);
        } else {
            $query->whereNull('url_path'); // Site-wide
        }
        
        return $query->get()
            ->groupBy('dimension_value')
            ->map(function ($metrics, $deviceType) use ($metricName) {
                // Get the latest metric for this device type
                $latest = $metrics->sortByDesc('date')->first();
                
                // Get threshold for this metric and device type
                $threshold = PerformanceThreshold::where('metric_name', $metricName)
                    ->where(function ($query) use ($deviceType) {
                        $query->whereNull('device_type')
                              ->orWhere('device_type', $deviceType);
                    })
                    ->whereNull('url_pattern')
                    ->first();
                    
                $status = 'unknown';
                if ($threshold) {
                    $status = $threshold->getStatus($latest->p75_value);
                }
                
                return [
                    'device_type' => $deviceType,
                    'value' => $latest->p75_value,
                    'status' => $status,
                    'sample_size' => $latest->sample_size,
                ];
            })
            ->values();
    }
    
    /**
     * Get performance data for top pages
     */
    public function getTopPagesPerformance(int $pageCount = 10, int $days = 7)
    {
        $startDate = now()->subDays($days)->startOfDay();
        
        // Get the most viewed pages first
        $topPages = PerformanceMetric::select('url_path')
            ->where('created_at', '>=', $startDate)
            ->groupBy('url_path')
            ->orderByRaw('COUNT(*) DESC')
            ->limit($pageCount)
            ->get()
            ->pluck('url_path');
            
        $results = [];
        
        foreach ($topPages as $urlPath) {
            $results[] = [
                'url_path' => $urlPath,
                'lcp' => $this->getCoreVitalSummary('lcp', $days, $urlPath),
                'cls' => $this->getCoreVitalSummary('cls', $days, $urlPath),
                'onload_time' => $this->getCoreVitalSummary('onload_time', $days, $urlPath),
            ];
        }
        
        return $results;
    }
    
    /**
     * Detect performance regressions
     */
    public function detectRegressions(int $days = 1)
    {
        $regressions = [];
        $metrics = ['lcp', 'cls', 'inp', 'onload_time'];
        
        foreach ($metrics as $metricName) {
            // Get threshold for determining significant regressions
            $threshold = PerformanceThreshold::where('metric_name', $metricName)
                ->whereNull('url_pattern')
                ->whereNull('device_type')
                ->first();
                
            if (!$threshold) {
                continue;
            }
            
            // Get current and previous period data
            $currentDate = now()->subDay()->toDateString();
            $previousDate = now()->subDays(2)->toDateString();
            
            $currentPeriod = AggregatedPerformanceMetric::where('metric_name', $metricName)
                ->where('date', $currentDate)
                ->where('aggregation_level', 'daily')
                ->whereNull('dimension')
                ->get();
                
            $previousPeriod = AggregatedPerformanceMetric::where('metric_name', $metricName)
                ->where('date', $previousDate)
                ->where('aggregation_level', 'daily')
                ->whereNull('dimension')
                ->get();
                
            // Group by url_path
            $currentByPath = $currentPeriod->keyBy('url_path');
            $previousByPath = $previousPeriod->keyBy('url_path');
            
            // Compare and find regressions
            foreach ($currentByPath as $path => $current) {
                if (isset($previousByPath[$path])) {
                    $previous = $previousByPath[$path];
                    
                    $percentChange = (($current->p75_value - $previous->p75_value) / $previous->p75_value) * 100;
                    
                    // Regression threshold varies by metric
                    $regressionThreshold = $metricName === 'cls' ? 10 : 20; // 10% for CLS, 20% for others
                    
                    if ($percentChange > $regressionThreshold) {
                        $regressions[] = [
                            'url_path' => $path ?: 'Site-wide',
                            'metric' => $metricName,
                            'current' => $current->p75_value,
                            'previous' => $previous->p75_value,
                            'change_percent' => round($percentChange, 1),
                            'severity' => $this->calculateSeverity($percentChange, $threshold, $current->p75_value),
                        ];
                    }
                }
            }
        }
        
        return $regressions;
    }
    
    /**
     * Calculate severity of a regression
     */
    private function calculateSeverity($percentChange, $threshold, $currentValue)
    {
        // High severity if:
        // 1. The change is more than 30%
        // 2. OR the current value is in the "poor" range and change is more than 15%
        if ($percentChange > 30 || ($threshold->isPoor($currentValue) && $percentChange > 15)) {
            return 'high';
        }
        
        // Medium severity if:
        // 1. The change is between 15-30%
        // 2. OR the current value is in the "needs improvement" range and change is more than 10%
        if ($percentChange > 15 || (!$threshold->isGood($currentValue) && $percentChange > 10)) {
            return 'medium';
        }
        
        return 'low';
    }
    
    /**
     * Calculate percentile value from an array
     */
    public function percentile($data, $percentile)
    {
        if ($data->isEmpty()) {
            return null;
        }
        
        $count = $data->count();
        $index = ceil($percentile / 100 * $count) - 1;
        
        return $data[$index < 0 ? 0 : $index];
    }
    
    /**
     * Aggregate hourly metrics
     */
    public function aggregateHourlyMetrics(Carbon $hour)
    {
        $startOfHour = $hour->copy()->startOfHour();
        $endOfHour = $hour->copy()->endOfHour();
        $date = $startOfHour->toDateString();
        
        Log::info("Aggregating hourly metrics for {$startOfHour->format('Y-m-d H:i')}");
        
        // Get all metrics from the previous hour
        $metrics = PerformanceMetric::whereBetween('created_at', [$startOfHour, $endOfHour])->get();
        
        if ($metrics->isEmpty()) {
            Log::info("No metrics found for this hour");
            return 0;
        }
        
        // Metrics to aggregate
        $metricFields = ['lcp', 'fcp', 'cls', 'inp', 'onload_time'];
        $dimensionFields = ['device_type', 'browser_family'];
        
        $aggregationCount = 0;
        
        // First, aggregate site-wide metrics (no URL path)
        foreach ($metricFields as $metricField) {
            $values = $metrics->filter(function ($metric) use ($metricField) {
                return $metric->{$metricField} !== null;
            })->pluck($metricField)->sort()->values();
            
            if ($values->isEmpty()) {
                continue;
            }
            
            // Create site-wide aggregation
            AggregatedPerformanceMetric::updateOrCreate(
                [
                    'date' => $date,
                    'url_path' => null,
                    'metric_name' => $metricField,
                    'dimension' => null,
                    'dimension_value' => null,
                    'hour' => $startOfHour->hour,
                    'aggregation_level' => 'hourly',
                ],
                [
                    'sample_size' => $values->count(),
                    'p50_value' => $this->percentile($values, 50),
                    'p75_value' => $this->percentile($values, 75),
                    'p90_value' => $this->percentile($values, 90),
                    'p95_value' => $this->percentile($values, 95),
                    'p99_value' => $this->percentile($values, 99),
                    'avg_value' => $values->avg(),
                    'min_value' => $values->min(),
                    'max_value' => $values->max(),
                ]
            );
            
            $aggregationCount++;
            
            // Create dimension-specific aggregations
            foreach ($dimensionFields as $dimensionField) {
                $dimensionGroups = $metrics->filter(function ($metric) use ($metricField) {
                    return $metric->{$metricField} !== null;
                })->groupBy($dimensionField);
                
                foreach ($dimensionGroups as $dimensionValue => $dimensionMetrics) {
                    $dimensionValues = $dimensionMetrics->pluck($metricField)->sort()->values();
                    
                    AggregatedPerformanceMetric::updateOrCreate(
                        [
                            'date' => $date,
                            'url_path' => null,
                            'metric_name' => $metricField,
                            'dimension' => $dimensionField,
                            'dimension_value' => $dimensionValue,
                            'hour' => $startOfHour->hour,
                            'aggregation_level' => 'hourly',
                        ],
                        [
                            'sample_size' => $dimensionValues->count(),
                            'p50_value' => $this->percentile($dimensionValues, 50),
                            'p75_value' => $this->percentile($dimensionValues, 75),
                            'p90_value' => $this->percentile($dimensionValues, 90),
                            'p95_value' => $this->percentile($dimensionValues, 95),
                            'p99_value' => $this->percentile($dimensionValues, 99),
                            'avg_value' => $dimensionValues->avg(),
                            'min_value' => $dimensionValues->min(),
                            'max_value' => $dimensionValues->max(),
                        ]
                    );
                    
                    $aggregationCount++;
                }
            }
        }
        
        // Next, aggregate by URL path
        $urlGroups = $metrics->groupBy('url_path');
        
        foreach ($urlGroups as $urlPath => $urlMetrics) {
            foreach ($metricFields as $metricField) {
                $values = $urlMetrics->filter(function ($metric) use ($metricField) {
                    return $metric->{$metricField} !== null;
                })->pluck($metricField)->sort()->values();
                
                if ($values->isEmpty()) {
                    continue;
                }
                
                // Create URL-specific aggregation
                AggregatedPerformanceMetric::updateOrCreate(
                    [
                        'date' => $date,
                        'url_path' => $urlPath,
                        'metric_name' => $metricField,
                        'dimension' => null,
                        'dimension_value' => null,
                        'hour' => $startOfHour->hour,
                        'aggregation_level' => 'hourly',
                    ],
                    [
                        'sample_size' => $values->count(),
                        'p50_value' => $this->percentile($values, 50),
                        'p75_value' => $this->percentile($values, 75),
                        'p90_value' => $this->percentile($values, 90),
                        'p95_value' => $this->percentile($values, 95),
                        'p99_value' => $this->percentile($values, 99),
                        'avg_value' => $values->avg(),
                        'min_value' => $values->min(),
                        'max_value' => $values->max(),
                    ]
                );
                
                $aggregationCount++;
                
                // URL + dimension aggregations
                foreach ($dimensionFields as $dimensionField) {
                    $dimensionGroups = $urlMetrics->filter(function ($metric) use ($metricField) {
                        return $metric->{$metricField} !== null;
                    })->groupBy($dimensionField);
                    
                    foreach ($dimensionGroups as $dimensionValue => $dimensionMetrics) {
                        $dimensionValues = $dimensionMetrics->pluck($metricField)->sort()->values();
                        
                        if ($dimensionValues->count() < 5) {
                            // Skip if sample size is too small
                            continue;
                        }
                        
                        AggregatedPerformanceMetric::updateOrCreate(
                            [
                                'date' => $date,
                                'url_path' => $urlPath,
                                'metric_name' => $metricField,
                                'dimension' => $dimensionField,
                                'dimension_value' => $dimensionValue,
                                'hour' => $startOfHour->hour,
                                'aggregation_level' => 'hourly',
                            ],
                            [
                                'sample_size' => $dimensionValues->count(),
                                'p50_value' => $this->percentile($dimensionValues, 50),
                                'p75_value' => $this->percentile($dimensionValues, 75),
                                'p90_value' => $this->percentile($dimensionValues, 90),
                                'p95_value' => $this->percentile($dimensionValues, 95),
                                'p99_value' => $this->percentile($dimensionValues, 99),
                                'avg_value' => $dimensionValues->avg(),
                                'min_value' => $dimensionValues->min(),
                                'max_value' => $dimensionValues->max(),
                            ]
                        );
                        
                        $aggregationCount++;
                    }
                }
            }
        }
        
        Log::info("Created {$aggregationCount} hourly aggregations");
        
        return $aggregationCount;
    }
    
    /**
     * Aggregate daily metrics
     */
    public function aggregateDailyMetrics(Carbon $date)
    {
        $startOfDay = $date->copy()->startOfDay();
        $endOfDay = $date->copy()->endOfDay();
        $dateString = $startOfDay->toDateString();
        
        Log::info("Aggregating daily metrics for {$dateString}");
        
        // Get hourly aggregations for this day
        $hourlyAggregations = AggregatedPerformanceMetric::where('date', $dateString)
            ->where('aggregation_level', 'hourly')
            ->get();
            
        if ($hourlyAggregations->isEmpty()) {
            Log::info("No hourly aggregations found for this day");
            return 0;
        }
        
        // Group by url_path, metric_name, dimension, dimension_value
        $groups = $hourlyAggregations->groupBy(function ($item) {
            return implode(':', [
                $item->url_path ?? 'null',
                $item->metric_name,
                $item->dimension ?? 'null',
                $item->dimension_value ?? 'null',
            ]);
        });
        
        $aggregationCount = 0;
        
        foreach ($groups as $groupKey => $groupItems) {
            list($urlPath, $metricName, $dimension, $dimensionValue) = explode(':', $groupKey);
            
            // Convert 'null' strings back to null
            $urlPath = $urlPath === 'null' ? null : $urlPath;
            $dimension = $dimension === 'null' ? null : $dimension;
            $dimensionValue = $dimensionValue === 'null' ? null : $dimensionValue;
            
            // Calculate weighted averages based on sample size
            $totalSamples = $groupItems->sum('sample_size');
            
            if ($totalSamples === 0) {
                continue;
            }
            
            // Combine all values from hourly aggregations
            $allValues = [];
            foreach ($groupItems as $item) {
                // We don't have the raw values, so we'll approximate using the percentiles
                // This is not perfect but gives a reasonable approximation
                $weight = $item->sample_size / $totalSamples;
                
                $allValues[] = [
                    'p50' => $item->p50_value,
                    'p75' => $item->p75_value,
                    'p90' => $item->p90_value,
                    'p95' => $item->p95_value,
                    'p99' => $item->p99_value,
                    'avg' => $item->avg_value,
                    'min' => $item->min_value,
                    'max' => $item->max_value,
                    'weight' => $weight,
                ];
            }
            
            // Calculate weighted values
            $p50 = array_sum(array_map(function ($item) { return $item['p50'] * $item['weight']; }, $allValues));
            $p75 = array_sum(array_map(function ($item) { return $item['p75'] * $item['weight']; }, $allValues));
            $p90 = array_sum(array_map(function ($item) { return $item['p90'] * $item['weight']; }, $allValues));
            $p95 = array_sum(array_map(function ($item) { return $item['p95'] * $item['weight']; }, $allValues));
            $p99 = array_sum(array_map(function ($item) { return $item['p99'] * $item['weight']; }, $allValues));
            $avg = array_sum(array_map(function ($item) { return $item['avg'] * $item['weight']; }, $allValues));
            
            // For min and max, we take the actual min and max
            $min = min(array_column($allValues, 'min'));
            $max = max(array_column($allValues, 'max'));
            
            AggregatedPerformanceMetric::updateOrCreate(
                [
                    'date' => $dateString,
                    'url_path' => $urlPath,
                    'metric_name' => $metricName,
                    'dimension' => $dimension,
                    'dimension_value' => $dimensionValue,
                    'hour' => null,
                    'aggregation_level' => 'daily',
                ],
                [
                    'sample_size' => $totalSamples,
                    'p50_value' => $p50,
                    'p75_value' => $p75,
                    'p90_value' => $p90,
                    'p95_value' => $p95,
                    'p99_value' => $p99,
                    'avg_value' => $avg,
                    'min_value' => $min,
                    'max_value' => $max,
                ]
            );
            
            $aggregationCount++;
        }
        
        Log::info("Created {$aggregationCount} daily aggregations");
        
        return $aggregationCount;
    }
}
