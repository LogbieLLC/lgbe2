<?php

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

// Set database connection for testing
DB::purge();

// Enable error reporting during tests
uses(TestCase::class, RefreshDatabase::class)
    ->beforeEach(function () {
        // Restore error handlers that might be removed by test code
        set_error_handler(null);
        set_exception_handler(null);
    })
    ->in(__DIR__);
