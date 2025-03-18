<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Performance Metrics Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration options for the performance metrics
    | collection and reporting system.
    |
    */

    // Report recipients
    'technical_report_recipients' => [
        'dev-team@example.com',
        'engineering-manager@example.com',
    ],
    
    'business_report_recipients' => [
        'product-manager@example.com',
        'marketing@example.com',
        'ceo@example.com',
    ],
    
    'alert_recipients' => [
        'on-call@example.com',
        'devops@example.com',
    ],
    
    // Integration settings
    'google_analytics' => [
        'enabled' => env('PERFORMANCE_GA_ENABLED', false),
        'view_id' => env('PERFORMANCE_GA_VIEW_ID'),
        'service_account_path' => storage_path('app/google/service-account.json'),
    ],
    
    // Alert thresholds
    'regression_thresholds' => [
        'lcp' => 20, // 20% change to trigger alert
        'cls' => 10, // 10% change to trigger alert
        'inp' => 20,
        'onload_time' => 25,
    ],
    
    // Performance thresholds
    'performance_targets' => [
        'lcp' => [
            'good' => 2500, // ms
            'poor' => 4000, // ms
        ],
        'cls' => [
            'good' => 0.1,
            'poor' => 0.25,
        ],
        'inp' => [
            'good' => 200, // ms
            'poor' => 500, // ms
        ],
        'onload_time' => [
            'good' => 3000, // ms
            'poor' => 6000, // ms
        ],
    ],
    
    // Notification channels
    'slack_webhook' => env('PERFORMANCE_SLACK_WEBHOOK'),
    
    // Sampling rate (percentage of page loads to measure)
    'sampling_rate' => env('PERFORMANCE_SAMPLING_RATE', 10), // 10%
    
    // Data retention (in days)
    'retention' => [
        'raw_metrics' => 30,
        'hourly_aggregations' => 7,
        'daily_aggregations' => 90,
        'weekly_aggregations' => 365,
        'monthly_aggregations' => 1095, // 3 years
    ],
    
    // Dashboard access
    'dashboard_access' => [
        'roles' => ['admin', 'developer'],
        'emails' => [],
    ],
];
