<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerformanceThreshold extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'metric_name',
        'url_pattern',
        'good_threshold',
        'poor_threshold',
        'device_type',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'good_threshold' => 'float',
        'poor_threshold' => 'float',
    ];

    /**
     * Scope for specific metrics
     */
    public function scopeForMetric($query, $metricName)
    {
        return $query->where('metric_name', $metricName);
    }

    /**
     * Scope for global thresholds (no URL pattern or device type)
     */
    public function scopeGlobal($query)
    {
        return $query->whereNull('url_pattern')->whereNull('device_type');
    }

    /**
     * Scope for specific device types
     */
    public function scopeForDeviceType($query, $deviceType)
    {
        return $query->where('device_type', $deviceType);
    }

    /**
     * Get the most specific threshold for a given URL and device type
     */
    public static function getMostSpecificThreshold($metricName, $urlPath = null, $deviceType = null)
    {
        $query = self::where('metric_name', $metricName);

        // Order by specificity - most specific first
        $query->orderByRaw('
            CASE 
                WHEN url_pattern IS NOT NULL AND device_type IS NOT NULL THEN 1
                WHEN url_pattern IS NOT NULL THEN 2
                WHEN device_type IS NOT NULL THEN 3
                ELSE 4
            END
        ');

        // Filter by URL pattern if provided
        if ($urlPath) {
            $query->where(function ($q) use ($urlPath) {
                $q->whereNull('url_pattern')
                  ->orWhereRaw("? LIKE url_pattern", [$urlPath]);
            });
        }

        // Filter by device type if provided
        if ($deviceType) {
            $query->where(function ($q) use ($deviceType) {
                $q->whereNull('device_type')
                  ->orWhere('device_type', $deviceType);
            });
        }

        return $query->first();
    }

    /**
     * Check if a value meets the good threshold
     */
    public function isGood($value)
    {
        return $value <= $this->good_threshold;
    }

    /**
     * Check if a value meets the poor threshold
     */
    public function isPoor($value)
    {
        return $value >= $this->poor_threshold;
    }

    /**
     * Get the status for a given value
     */
    public function getStatus($value)
    {
        if ($this->isGood($value)) {
            return 'good';
        } elseif ($this->isPoor($value)) {
            return 'poor';
        } else {
            return 'needs-improvement';
        }
    }
}
