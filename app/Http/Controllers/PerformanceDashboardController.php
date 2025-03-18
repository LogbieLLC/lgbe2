<?php

namespace App\Http\Controllers;

use App\Models\AggregatedPerformanceMetric;
use App\Models\PerformanceMetric;
use App\Models\PerformanceThreshold;
use App\Services\PerformanceMetricsService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PerformanceDashboardController extends Controller
{
    protected $performanceMetricsService;
    
    public function __construct(PerformanceMetricsService $performanceMetricsService)
    {
        $this->performanceMetricsService = $performanceMetricsService;
        $this->middleware('auth'); // Ensure dashboard is protected
    }
    
    /**
     * Show main performance dashboard
     */
    public function index()
    {
        // Get summary data for the dashboard
        $summaryData = $this->getDashboardSummary();
        
        return Inertia::render('Performance/Dashboard', [
            'summaryData' => $summaryData,
        ]);
    }
    
    /**
     * Get dashboard summary data
     */
    private function getDashboardSummary()
    {
        return [
            'core_vitals' => [
                $this->performanceMetricsService->getCoreVitalSummary('lcp'),
                $this->performanceMetricsService->getCoreVitalSummary('cls'),
                $this->performanceMetricsService->getCoreVitalSummary('inp'),
            ],
            'onload_time' => $this->performanceMetricsService->getCoreVitalSummary('onload_time'),
            'device_breakdown' => $this->performanceMetricsService->getDeviceTypeBreakdown('lcp'),
            'browser_breakdown' => $this->getTopBrowsers(),
            'page_breakdown' => $this->performanceMetricsService->getTopPagesPerformance(5),
            'regressions' => $this->performanceMetricsService->detectRegressions(),
        ];
    }
    
    /**
     * Get performance trends data for API
     */
    public function getTrends(Request $request)
    {
        $metricName = $request->input('metric', 'lcp');
        $days = (int) $request->input('days', 30);
        $urlPath = $request->input('url_path');
        $dimension = $request->input('dimension');
        
        $trendData = $this->performanceMetricsService->getMetricTrend(
            $metricName, 
            $days, 
            $urlPath, 
            $dimension
        );
        
        return response()->json($trendData);
    }
    
    /**
     * Show page details dashboard
     */
    public function pageDetails(Request $request, $urlPath)
    {
        $pageData = $this->getPagePerformanceDetails($urlPath);
        
        return Inertia::render('Performance/PageDetails', [
            'pageData' => $pageData,
            'urlPath' => $urlPath,
        ]);
    }
    
    /**
     * Get detailed performance data for a specific page
     */
    private function getPagePerformanceDetails($urlPath)
    {
        return [
            'metrics' => [
                'lcp' => $this->performanceMetricsService->getCoreVitalSummary('lcp', 30, $urlPath),
                'fcp' => $this->performanceMetricsService->getCoreVitalSummary('fcp', 30, $urlPath),
                'cls' => $this->performanceMetricsService->getCoreVitalSummary('cls', 30, $urlPath),
                'inp' => $this->performanceMetricsService->getCoreVitalSummary('inp', 30, $urlPath),
                'onload_time' => $this->performanceMetricsService->getCoreVitalSummary('onload_time', 30, $urlPath),
            ],
            'device_breakdown' => $this->performanceMetricsService->getDeviceTypeBreakdown('lcp', 30, $urlPath),
            'browser_breakdown' => $this->getBrowserBreakdown('lcp', 30, $urlPath),
            'trends' => [
                'lcp' => $this->performanceMetricsService->getMetricTrend('lcp', 30, $urlPath),
                'onload_time' => $this->performanceMetricsService->getMetricTrend('onload_time', 30, $urlPath),
            ],
        ];
    }
    
    /**
     * Get top browsers by usage
     */
    private function getTopBrowsers($days = 7)
    {
        $startDate = now()->subDays($days)->startOfDay();
        
        $browsers = AggregatedPerformanceMetric::where('date', '>=', $startDate->toDateString())
            ->where('dimension', 'browser_family')
            ->where('metric_name', 'lcp') // Use LCP as it's commonly measured
            ->where('aggregation_level', 'daily')
            ->whereNull('url_path') // Site-wide
            ->get()
            ->groupBy('dimension_value');
            
        $results = [];
        
        foreach ($browsers as $browser => $metrics) {
            // Get the latest metric for this browser
            $latest = $metrics->sortByDesc('date')->first();
            
            $results[] = [
                'browser' => $browser,
                'sample_size' => $latest->sample_size,
                'lcp' => $latest->p75_value,
            ];
        }
        
        // Sort by sample size (popularity)
        usort($results, function ($a, $b) {
            return $b['sample_size'] <=> $a['sample_size'];
        });
        
        return array_slice($results, 0, 5); // Return top 5
    }
    
    /**
     * Get browser breakdown for a specific page and metric
     */
    private function getBrowserBreakdown($metricName, $days = 30, $urlPath = null)
    {
        $startDate = now()->subDays($days)->startOfDay();
        
        $query = AggregatedPerformanceMetric::where('date', '>=', $startDate->toDateString())
            ->where('dimension', 'browser_family')
            ->where('metric_name', $metricName)
            ->where('aggregation_level', 'daily');
            
        if ($urlPath) {
            $query->where('url_path', $urlPath);
        } else {
            $query->whereNull('url_path'); // Site-wide
        }
        
        $browsers = $query->get()->groupBy('dimension_value');
        
        $results = [];
        
        foreach ($browsers as $browser => $metrics) {
            // Get the latest metric for this browser
            $latest = $metrics->sortByDesc('date')->first();
            
            // Get threshold for this metric
            $threshold = PerformanceThreshold::where('metric_name', $metricName)
                ->whereNull('url_pattern')
                ->whereNull('device_type')
                ->first();
                
            $status = 'unknown';
            if ($threshold) {
                $status = $threshold->getStatus($latest->p75_value);
            }
            
            $results[] = [
                'browser' => $browser,
                'value' => $latest->p75_value,
                'status' => $status,
                'sample_size' => $latest->sample_size,
            ];
        }
        
        // Sort by sample size (popularity)
        usort($results, function ($a, $b) {
            return $b['sample_size'] <=> $a['sample_size'];
        });
        
        return $results;
    }
    
    /**
     * Generate and download a report
     */
    public function downloadReport(Request $request)
    {
        $reportType = $request->input('type', 'technical');
        $format = $request->input('format', 'json');
        $days = (int) $request->input('days', 30);
        
        if ($reportType === 'technical') {
            $report = $this->generateTechnicalReport($days);
        } else {
            $report = $this->generateBusinessReport($days);
        }
        
        if ($format === 'json') {
            return response()->json($report);
        } elseif ($format === 'csv') {
            return $this->generateCsvReport($report);
        }
        
        return response()->json($report);
    }
    
    /**
     * Generate technical report data
     */
    private function generateTechnicalReport($days = 30)
    {
        return [
            'title' => 'Technical Performance Report - ' . now()->toDateString(),
            'period' => 'Last ' . $days . ' days',
            'generated_at' => now()->toDateTimeString(),
            'core_vitals' => [
                $this->performanceMetricsService->getCoreVitalSummary('lcp', $days),
                $this->performanceMetricsService->getCoreVitalSummary('cls', $days),
                $this->performanceMetricsService->getCoreVitalSummary('inp', $days),
            ],
            'onload_time' => $this->performanceMetricsService->getCoreVitalSummary('onload_time', $days),
            'device_breakdown' => $this->performanceMetricsService->getDeviceTypeBreakdown('lcp', $days),
            'browser_breakdown' => $this->getTopBrowsers($days),
            'page_breakdown' => $this->performanceMetricsService->getTopPagesPerformance(10, $days),
            'regressions' => $this->performanceMetricsService->detectRegressions(),
        ];
    }
    
    /**
     * Generate business report data
     */
    private function generateBusinessReport($days = 30)
    {
        // This would typically include business metrics correlation
        // For now, we'll just include core performance data
        return [
            'title' => 'Business Performance Impact Report - ' . now()->format('F Y'),
            'period' => 'Last ' . $days . ' days',
            'generated_at' => now()->toDateTimeString(),
            'executive_summary' => $this->generateExecutiveSummary($days),
            'core_vitals' => [
                $this->performanceMetricsService->getCoreVitalSummary('lcp', $days),
                $this->performanceMetricsService->getCoreVitalSummary('cls', $days),
            ],
            'top_pages' => $this->performanceMetricsService->getTopPagesPerformance(5, $days),
            'device_breakdown' => $this->performanceMetricsService->getDeviceTypeBreakdown('lcp', $days),
        ];
    }
    
    /**
     * Generate executive summary
     */
    private function generateExecutiveSummary($days = 30)
    {
        $lcp = $this->performanceMetricsService->getCoreVitalSummary('lcp', $days);
        $cls = $this->performanceMetricsService->getCoreVitalSummary('cls', $days);
        $onloadTime = $this->performanceMetricsService->getCoreVitalSummary('onload_time', $days);
        
        $lcpStatus = $lcp['status'];
        $clsStatus = $cls['status'];
        
        $summary = "Over the past {$days} days, our site's performance has been ";
        
        if ($lcpStatus === 'good' && $clsStatus === 'good') {
            $summary .= "strong, with both Largest Contentful Paint (LCP) and Cumulative Layout Shift (CLS) metrics meeting 'good' thresholds. ";
        } elseif ($lcpStatus === 'poor' || $clsStatus === 'poor') {
            $summary .= "concerning, with " . ($lcpStatus === 'poor' ? 'loading speed (LCP)' : '') . 
                        ($lcpStatus === 'poor' && $clsStatus === 'poor' ? ' and ' : '') .
                        ($clsStatus === 'poor' ? 'visual stability (CLS)' : '') . " not meeting acceptable thresholds. ";
        } else {
            $summary .= "mixed, with some metrics meeting targets while others need improvement. ";
        }
        
        if (isset($lcp['change']) && $lcp['change'] !== 'N/A') {
            $lcpChange = (float) str_replace('%', '', $lcp['change']);
            if ($lcpChange < 0) {
                $summary .= "Loading performance has improved by " . abs($lcpChange) . "% compared to the previous period. ";
            } elseif ($lcpChange > 5) {
                $summary .= "Loading performance has degraded by {$lcpChange}% compared to the previous period. ";
            }
        }
        
        $summary .= "Average page load time is " . round($onloadTime['value'] / 1000, 2) . " seconds. ";
        
        // Add device-specific insights
        $deviceBreakdown = $this->performanceMetricsService->getDeviceTypeBreakdown('lcp', $days);
        $mobileData = collect($deviceBreakdown)->firstWhere('device_type', 'mobile');
        $desktopData = collect($deviceBreakdown)->firstWhere('device_type', 'desktop');
        
        if ($mobileData && $desktopData) {
            $mobileDesktopDiff = (($mobileData['value'] - $desktopData['value']) / $desktopData['value']) * 100;
            if ($mobileDesktopDiff > 20) {
                $summary .= "Mobile performance is significantly worse than desktop, with mobile pages loading " . 
                            round($mobileDesktopDiff, 0) . "% slower. This represents an opportunity for optimization. ";
            }
        }
        
        return $summary;
    }
    
    /**
     * Generate CSV report
     */
    private function generateCsvReport($reportData)
    {
        $output = fopen('php://temp', 'r+');
        
        // Write headers
        fputcsv($output, ['Metric', 'Value', 'Status', 'Change', 'Sample Size']);
        
        // Write core vitals
        foreach ($reportData['core_vitals'] as $metric) {
            fputcsv($output, [
                $metric['name'],
                $metric['value'],
                $metric['status'],
                $metric['change'],
                $metric['sample_size'],
            ]);
        }
        
        // Write onload time
        fputcsv($output, [
            $reportData['onload_time']['name'],
            $reportData['onload_time']['value'],
            $reportData['onload_time']['status'],
            $reportData['onload_time']['change'],
            $reportData['onload_time']['sample_size'],
        ]);
        
        // Add a blank line
        fputcsv($output, []);
        
        // Write page breakdown header
        fputcsv($output, ['Page', 'LCP', 'LCP Status', 'CLS', 'CLS Status', 'Onload Time', 'Onload Status']);
        
        // Write page breakdown
        foreach ($reportData['page_breakdown'] as $page) {
            fputcsv($output, [
                $page['url_path'],
                $page['lcp']['value'],
                $page['lcp']['status'],
                $page['cls']['value'],
                $page['cls']['status'],
                $page['onload_time']['value'],
                $page['onload_time']['status'],
            ]);
        }
        
        // Rewind the file pointer
        rewind($output);
        
        // Get the content
        $content = stream_get_contents($output);
        
        // Close the file
        fclose($output);
        
        // Return as a download
        return response($content, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="performance_report_' . now()->format('Y-m-d') . '.csv"',
        ]);
    }
}
