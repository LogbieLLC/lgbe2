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
        Schema::create('performance_metrics', function (Blueprint $table) {
            $table->id();
            $table->string('url_path', 255);                // The page path that was measured
            $table->string('route_name', 100)->nullable();  // Laravel route name if available

            // Core Web Vitals and metrics
            $table->float('lcp')->nullable();               // Largest Contentful Paint in ms
            $table->float('fcp')->nullable();               // First Contentful Paint in ms
            $table->float('cls')->nullable();               // Cumulative Layout Shift score
            $table->float('inp')->nullable();               // Interaction to Next Paint in ms
            $table->float('onload_time')->nullable();       // Navigation onload time in ms

            // Contextual data (anonymized)
            $table->string('device_type', 20);              // mobile, tablet, desktop
            $table->string('browser_family', 30);           // chrome, firefox, safari, etc.
            $table->string('browser_version', 20)->nullable();
            $table->string('os_family', 20);                // windows, macos, android, ios
            $table->string('country', 2)->nullable();       // ISO country code
            $table->string('region', 50)->nullable();       // Geographic region
            $table->integer('viewport_width')->nullable();
            $table->integer('viewport_height')->nullable();

            // Network information if available
            $table->string('connection_type', 20)->nullable(); // 4g, wifi, etc.
            $table->float('effective_bandwidth')->nullable();  // in Mbps

            // Privacy-focused identifiers
            $table->string('session_hash', 64);             // Anonymized session identifier
            $table->boolean('is_authenticated')->default(false); // Whether user is logged in (no user id stored)

            // Additional data
            $table->json('extra_metrics')->nullable();      // For custom or future metrics
            $table->timestamps();

            // Indexes for efficient querying
            $table->index('created_at');                    // For time-based queries
            $table->index(['url_path', 'created_at']);      // For page-specific analysis
            $table->index(['device_type', 'created_at']);   // For device-specific analysis
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('performance_metrics');
    }
};
