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
        Schema::create('performance_thresholds', function (Blueprint $table) {
            $table->id();
            $table->string('metric_name', 50);              // lcp, fcp, cls, inp, onload_time
            $table->string('url_pattern', 255)->nullable(); // NULL for site-wide, regex for specific pages
            $table->float('good_threshold');                // Value below which is considered good
            $table->float('poor_threshold');                // Value above which is considered poor
            $table->string('device_type', 20)->nullable();  // NULL for all devices or specific device type
            $table->timestamps();

            // Ensure unique combinations
            $table->unique(['metric_name', 'url_pattern', 'device_type'], 'unique_threshold');
        });

        // Insert default thresholds based on Core Web Vitals
        DB::table('performance_thresholds')->insert([
            [
                'metric_name' => 'lcp',
                'url_pattern' => null,
                'good_threshold' => 2500, // 2.5 seconds
                'poor_threshold' => 4000, // 4 seconds
                'device_type' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'metric_name' => 'cls',
                'url_pattern' => null,
                'good_threshold' => 0.1,
                'poor_threshold' => 0.25,
                'device_type' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'metric_name' => 'inp',
                'url_pattern' => null,
                'good_threshold' => 200, // 200 milliseconds
                'poor_threshold' => 500, // 500 milliseconds
                'device_type' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'metric_name' => 'onload_time',
                'url_pattern' => null,
                'good_threshold' => 3000, // 3 seconds
                'poor_threshold' => 6000, // 6 seconds
                'device_type' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('performance_thresholds');
    }
};
