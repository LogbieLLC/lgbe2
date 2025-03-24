<?php

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

// Enable error reporting during tests
uses(TestCase::class, RefreshDatabase::class)
    ->beforeEach(function () {
        // Restore error handlers that might be removed by test code
        set_error_handler(null);
        set_exception_handler(null);

        // Ensure database connection is properly set up
        DB::purge();
        DB::reconnect();
    })
    ->afterEach(function () {
        // Clean up after each test
        set_error_handler(null);
        set_exception_handler(null);
    })
    ->in(__DIR__);
