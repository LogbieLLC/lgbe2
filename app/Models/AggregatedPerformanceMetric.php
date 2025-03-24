<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AggregatedPerformanceMetric extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'date',
        'url_path',
        'metric_name',
        'dimension',
        'dimension_value',
        'sample_size',
        'p50_value',
        'p75_value',
        'p90_value',
        'p95_value',
        'p99_value',
        'avg_value',
        'min_value',
        'max_value',
        'hour',
        'aggregation_level',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date' => 'date',
        'p50_value' => 'float',
        'p75_value' => 'float',
        'p90_value' => 'float',
        'p95_value' => 'float',
        'p99_value' => 'float',
        'avg_value' => 'float',
        'min_value' => 'float',
        'max_value' => 'float',
        'hour' => 'integer',
        'sample_size' => 'integer',
    ];

    /**
     * Scope for specific metrics
     */
    public function scopeForMetric($query, $metricName)
    {
        return $query->where('metric_name', $metricName);
    }

    /**
     * Scope for specific time periods
     */
    public function scopeForDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Scope for specific pages
     */
    public function scopeForPath($query, $path)
    {
        return $query->where('url_path', $path);
    }

    /**
     * Scope for site-wide metrics (no specific path)
     */
    public function scopeSiteWide($query)
    {
        return $query->whereNull('url_path');
    }

    /**
     * Scope for specific aggregation levels
     */
    public function scopeForAggregationLevel($query, $level)
    {
        return $query->where('aggregation_level', $level);
    }

    /**
     * Scope for specific dimensions
     */
    public function scopeForDimension($query, $dimension, $value = null)
    {
        $query = $query->where('dimension', $dimension);

        if ($value !== null) {
            $query->where('dimension_value', $value);
        }

        return $query;
    }

    /**
     * Get the performance status for this aggregated metric
     */
    public function getMetricStatus()
    {
        $threshold = PerformanceThreshold::where('metric_name', $this->metric_name)
            ->where(function ($query) {
                $query->whereNull('url_pattern');

                if ($this->url_path) {
                    $query->orWhere(function ($q) {
                        // Match URL pattern if specified
                        // This is a simplified version - in production you'd use regex matching
                        $q->whereRaw("? LIKE url_pattern", [$this->url_path]);
                    });
                }
            })
            ->where(function ($query) {
                $query->whereNull('device_type');

                if ($this->dimension === 'device_type') {
                    $query->orWhere('device_type', $this->dimension_value);
                }
            })
            ->first();

        if (!$threshold) {
            return 'unknown';
        }

        // Use p75 value for Core Web Vitals assessment
        if ($this->p75_value <= $threshold->good_threshold) {
            return 'good';
        } elseif ($this->p75_value >= $threshold->poor_threshold) {
            return 'poor';
        } else {
            return 'needs-improvement';
        }
    }

    /**
     * Calculate the percentage change from a previous period
     */
    public function getPercentageChangeFrom(AggregatedPerformanceMetric $previous)
    {
        if ($previous->p75_value == 0) {
            return null; // Avoid division by zero
        }

        return (($this->p75_value - $previous->p75_value) / $previous->p75_value) * 100;
    }
}
