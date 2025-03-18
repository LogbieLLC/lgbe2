<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('aggregated_performance_metrics', function (Blueprint $table) {
            $table->id();
            $table->date('date');                           // Aggregation date
            $table->string('url_path', 255)->nullable();    // Page path or NULL for site-wide
            $table->string('metric_name', 50);              // lcp, fcp, cls, inp, onload_time
            $table->string('dimension', 50)->nullable();    // device_type, browser_family, etc.
            $table->string('dimension_value', 50)->nullable(); // mobile, chrome, etc.
            
            // Statistical aggregations
            $table->integer('sample_size');                 // Number of samples in this aggregation
            $table->float('p50_value');                     // 50th percentile (median)
            $table->float('p75_value');                     // 75th percentile (Core Web Vitals focus)
            $table->float('p90_value');                     // 90th percentile 
            $table->float('p95_value');                     // 95th percentile
            $table->float('p99_value');                     // 99th percentile
            $table->float('avg_value');                     // Average value
            $table->float('min_value');                     // Minimum value
            $table->float('max_value');                     // Maximum value
            
            // Time dimensions
            $table->tinyInteger('hour')->nullable();        // 0-23 for hourly aggregations
            $table->string('aggregation_level', 20);        // hourly, daily, weekly, monthly
            
            $table->timestamps();
            
            // Unique constraint
            $table->unique(
                ['date', 'url_path', 'metric_name', 'dimension', 'dimension_value', 'hour', 'aggregation_level'],
                'unique_aggregation'
            );
                   
            // Indexes
            $table->index(['date', 'metric_name']);
            $table->index(['url_path', 'metric_name', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aggregated_performance_metrics');
    }
};
