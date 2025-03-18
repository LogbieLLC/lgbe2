# Performance Metrics System

This document provides an overview of the in-house page load reporting system implemented in this application. The system is designed to measure and report page load speeds for site visitors, providing insights into user experience and helping identify performance bottlenecks.

## Overview

The performance metrics system collects real user metrics (RUM) to monitor and improve website performance. It focuses on key performance metrics like Largest Contentful Paint (LCP) and onload time, providing insights into user-perceived and technical load times.

## Key Features

- **Client-side measurement** using the web-vitals library and Performance API
- **Privacy-focused** data collection with anonymized identifiers
- **Comprehensive metrics** including Core Web Vitals (LCP, CLS, INP) and traditional metrics
- **Data aggregation** for efficient storage and analysis
- **Interactive dashboard** for visualizing performance data
- **Automated reporting** for technical and business stakeholders
- **Regression detection** to identify performance issues early

## Architecture

The system consists of the following components:

1. **Client-side collection**: JavaScript module that measures performance metrics in the user's browser
2. **API endpoint**: Server endpoint that receives and validates metrics data
3. **Database storage**: Tables for raw metrics and aggregated data
4. **Aggregation jobs**: Scheduled tasks that process and aggregate metrics data
5. **Dashboard**: Web interface for visualizing and analyzing performance data
6. **Reporting system**: Automated reports for stakeholders

## Key Metrics

The system tracks the following metrics:

- **Largest Contentful Paint (LCP)**: Time when the largest content element becomes visible
  - Good: < 2.5 seconds
  - Needs improvement: 2.5-4.0 seconds
  - Poor: > 4.0 seconds

- **Cumulative Layout Shift (CLS)**: Visual stability during page load
  - Good: < 0.1
  - Needs improvement: 0.1-0.25
  - Poor: > 0.25

- **Interaction to Next Paint (INP)**: Responsiveness to user interactions
  - Good: < 200 milliseconds
  - Needs improvement: 200-500 milliseconds
  - Poor: > 500 milliseconds

- **First Contentful Paint (FCP)**: Time to first content visibility
  - Good: < 1.8 seconds
  - Needs improvement: 1.8-3.0 seconds
  - Poor: > 3.0 seconds

- **Onload Time**: Time from navigation start to the full page load event
  - Good: < 3.0 seconds
  - Needs improvement: 3.0-6.0 seconds
  - Poor: > 6.0 seconds

## Database Schema

The system uses two main tables:

1. **performance_metrics**: Stores raw metrics data
   - URL path, route name
   - Core Web Vitals (LCP, CLS, INP)
   - Traditional metrics (onload time, FCP)
   - Contextual data (device, browser, viewport)
   - Privacy-focused identifiers

2. **aggregated_performance_metrics**: Stores aggregated metrics data
   - Statistical aggregations (p50, p75, p90, p95, p99, avg, min, max)
   - Different time granularities (hourly, daily, weekly, monthly)
   - Dimension breakdowns (device type, browser)

3. **performance_thresholds**: Stores threshold values for metrics
   - Good and poor thresholds for each metric
   - URL pattern-specific thresholds
   - Device-specific thresholds

## Data Retention

The system implements a tiered data retention strategy:

- Raw metrics: 30 days
- Hourly aggregations: 7 days
- Daily aggregations: 90 days
- Weekly aggregations: 1 year
- Monthly aggregations: 3+ years

## Dashboard

The performance dashboard provides:

- Core Web Vitals summary
- Performance trends over time
- Device and browser breakdowns
- Page-specific performance data
- Regression detection
- Recommendations for improvement

## Scheduled Tasks

The system runs the following scheduled tasks:

- Hourly aggregation: Every hour at 5 minutes past the hour
- Daily aggregation: Every day at 1:15 AM
- Weekly aggregation: Every Monday at 2:15 AM
- Monthly aggregation: 1st of every month at 3:15 AM
- Data cleanup: According to retention policy

## Configuration

The system can be configured in `config/performance.php`:

- Report recipients
- Google Analytics integration
- Alert thresholds
- Performance targets
- Sampling rate
- Data retention periods
- Dashboard access control

## Usage

### Viewing the Dashboard

The performance dashboard is available at `/performance` for authorized users.

### Accessing the API

The system provides the following API endpoints:

- `POST /api/metrics`: Submit performance metrics
- `GET /api/performance/trends`: Get performance trend data
- `GET /api/performance/reports/download`: Download performance reports

### Running Aggregation Jobs Manually

You can run aggregation jobs manually using the following commands:

```bash
# Run hourly aggregation
php artisan performance:aggregate hourly

# Run daily aggregation
php artisan performance:aggregate daily

# Run weekly aggregation
php artisan performance:aggregate weekly

# Run monthly aggregation
php artisan performance:aggregate monthly

# Run all aggregations
php artisan performance:aggregate all
```

## Implementation Details

### Client-Side Collection

The client-side collection is implemented in `resources/js/lib/performance-metrics.js`. It uses the web-vitals library to measure Core Web Vitals and the Performance API to measure onload time.

### Server-Side Processing

The server-side processing is implemented in:

- `app/Http/Controllers/MetricsController.php`: API endpoint for receiving metrics
- `app/Services/PerformanceMetricsService.php`: Service for processing and aggregating metrics
- `app/Console/Commands/AggregatePerformanceMetrics.php`: Command for running aggregation jobs

### Dashboard

The dashboard is implemented in:

- `app/Http/Controllers/PerformanceDashboardController.php`: Controller for the dashboard
- `resources/js/pages/Performance/Dashboard.vue`: Vue component for the dashboard
- `resources/js/pages/Performance/PageDetails.vue`: Vue component for page-specific details

## Privacy Considerations

The system is designed with privacy in mind:

- No personally identifiable information (PII) is collected
- Session identifiers are anonymized using hashing
- Users can opt out of measurement
- Data retention policies limit how long data is kept
- Aggregated data is used for long-term analysis

## Future Enhancements

Potential future enhancements include:

- Integration with lab-based testing tools (Lighthouse, WebPageTest)
- A/B testing integration
- Custom metric support
- Enhanced visualization options
- Machine learning for anomaly detection
- Real-time alerting
