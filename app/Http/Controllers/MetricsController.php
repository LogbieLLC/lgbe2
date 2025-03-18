<?php

namespace App\Http\Controllers;

use App\Models\PerformanceMetric;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Jenssegers\Agent\Agent;

class MetricsController extends Controller
{
    /**
     * Store a new performance metric.
     */
    public function store(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'metrics' => 'required|array',
            'metrics.*.name' => 'required|string',
            'metrics.*.value' => 'required|numeric',
            'metrics.*.path' => 'required|string',
            'context' => 'required|array',
            'context.userAgent' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        try {
            // Process user agent to extract device and browser info
            $agent = new Agent();
            $agent->setUserAgent($request->input('context.userAgent'));
            
            $deviceType = $agent->isPhone() ? 'mobile' : ($agent->isTablet() ? 'tablet' : 'desktop');
            $browserFamily = $agent->browser();
            $browserVersion = $agent->version($browserFamily);
            $osFamily = $agent->platform();
            
            // Create a session hash (anonymized identifier)
            $sessionHash = hash('sha256', $request->ip() . $request->input('context.userAgent'));
            
            // Process and store each metric
            foreach ($request->input('metrics') as $metric) {
                // Normalize metric name to lowercase
                $metricName = strtolower($metric['name']);
                
                // Map web-vitals metric names to our database fields
                $metricField = $this->mapMetricNameToField($metricName);
                
                if (!$metricField) {
                    // Skip unknown metrics or store them in extra_metrics
                    continue;
                }
                
                // Create the performance metric record
                $performanceMetric = new PerformanceMetric();
                $performanceMetric->url_path = $metric['path'];
                $performanceMetric->$metricField = $metric['value'];
                $performanceMetric->device_type = $deviceType;
                $performanceMetric->browser_family = $browserFamily;
                $performanceMetric->browser_version = $browserVersion;
                $performanceMetric->os_family = $osFamily;
                $performanceMetric->session_hash = $sessionHash;
                $performanceMetric->is_authenticated = $request->input('context.isAuthenticated', false);
                
                // Add viewport dimensions if available
                if ($request->has('context.viewport')) {
                    $performanceMetric->viewport_width = $request->input('context.viewport.width');
                    $performanceMetric->viewport_height = $request->input('context.viewport.height');
                }
                
                // Add connection info if available
                if ($request->has('context.connection')) {
                    $performanceMetric->connection_type = $request->input('context.connection.type');
                    $performanceMetric->effective_bandwidth = $request->input('context.connection.effectiveType');
                }
                
                // Add geolocation if available (this would typically be determined server-side)
                // For privacy reasons, we're only storing country and region, not precise location
                if ($request->has('context.geo')) {
                    $performanceMetric->country = $request->input('context.geo.country');
                    $performanceMetric->region = $request->input('context.geo.region');
                }
                
                // Store any extra metrics as JSON
                $extraMetrics = [];
                foreach ($metric as $key => $value) {
                    if (!in_array($key, ['name', 'value', 'path'])) {
                        $extraMetrics[$key] = $value;
                    }
                }
                
                if (!empty($extraMetrics)) {
                    $performanceMetric->extra_metrics = $extraMetrics;
                }
                
                $performanceMetric->save();
            }
            
            return response()->json(['status' => 'success'], 201);
        } catch (\Exception $e) {
            Log::error('Error storing performance metrics: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to store metrics'], 500);
        }
    }
    
    /**
     * Map web-vitals metric names to database fields
     */
    private function mapMetricNameToField($metricName)
    {
        $mapping = [
            'lcp' => 'lcp',
            'largest-contentful-paint' => 'lcp',
            'fcp' => 'fcp',
            'first-contentful-paint' => 'fcp',
            'cls' => 'cls',
            'cumulative-layout-shift' => 'cls',
            'inp' => 'inp',
            'interaction-to-next-paint' => 'inp',
            'load' => 'onload_time',
            'onload' => 'onload_time',
        ];
        
        return $mapping[$metricName] ?? null;
    }
}
