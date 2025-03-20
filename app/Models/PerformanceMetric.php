<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerformanceMetric extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'url_path',
        'route_name',
        'lcp',
        'fcp',
        'cls',
        'inp',
        'onload_time',
        'device_type',
        'browser_family',
        'browser_version',
        'os_family',
        'country',
        'region',
        'viewport_width',
        'viewport_height',
        'connection_type',
        'effective_bandwidth',
        'session_hash',
        'is_authenticated',
        'extra_metrics',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'lcp' => 'float',
        'fcp' => 'float',
        'cls' => 'float',
        'inp' => 'float',
        'onload_time' => 'float',
        'viewport_width' => 'integer',
        'viewport_height' => 'integer',
        'effective_bandwidth' => 'float',
        'is_authenticated' => 'boolean',
        'extra_metrics' => 'json',
    ];

    /**
     * Scope for core web vitals metrics
     */
    public function scopeCoreWebVitals($query)
    {
        return $query->whereNotNull('lcp')
                     ->orWhereNotNull('cls')
                     ->orWhereNotNull('inp');
    }

    /**
     * Scope for specific time periods
     */
    public function scopeLastDays($query, $days)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope for specific pages
     */
    public function scopeForPath($query, $path)
    {
        return $query->where('url_path', $path);
    }

    /**
     * Scope for device types
     */
    public function scopeForDeviceType($query, $deviceType)
    {
        return $query->where('device_type', $deviceType);
    }

    /**
     * Scope for browser families
     */
    public function scopeForBrowser($query, $browser)
    {
        return $query->where('browser_family', $browser);
    }

    /**
     * Get the performance status for a specific metric
     */
    public function getMetricStatus($metricName)
    {
        if (!$this->{$metricName}) {
            return null;
        }

        $threshold = PerformanceThreshold::where('metric_name', $metricName)
            ->where(function ($query) {
                $query->whereNull('url_pattern')
                      ->orWhere(function ($q) {
                          // Match URL pattern if specified
                          // This is a simplified version - in production you'd use regex matching
                          $q->whereRaw("? LIKE url_pattern", [$this->url_path]);
                      });
            })
            ->where(function ($query) {
                $query->whereNull('device_type')
                      ->orWhere('device_type', $this->device_type);
            })
            ->first();

        if (!$threshold) {
            return 'unknown';
        }

        if ($this->{$metricName} <= $threshold->good_threshold) {
            return 'good';
        } elseif ($this->{$metricName} >= $threshold->poor_threshold) {
            return 'poor';
        } else {
            return 'needs-improvement';
        }
    }
}
