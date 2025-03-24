<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Config\Repository;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Create SQLite database file if it doesn't exist
        $databasePath = database_path('database.sqlite');
        $databaseDir = dirname($databasePath);

        if (!file_exists($databaseDir)) {
            mkdir($databaseDir, 0777, true);
        }

        if (!file_exists($databasePath)) {
            touch($databasePath);
        }

        // Ensure config is bound for Laravel 11
        if (!$this->app->bound('config')) {
            $this->app->singleton('config', function () {
                return new Repository();
            });
        }
    }

    /**
     * Clean up the test environment.
     */
    protected function tearDown(): void
    {
        // First call parent tearDown to ensure proper cleanup
        parent::tearDown();
        
        // Restore error and exception handlers using Laravel's method
        $this->flushHandlersState();
    }
    
    /**
     * Flush the error and exception handlers state.
     * 
     * This is based on Laravel's HandleExceptions::flushHandlersState method.
     *
     * @return void
     */
    protected function flushHandlersState()
    {
        // Restore all exception handlers
        while (true) {
            $previousHandler = set_exception_handler(function (\Throwable $e) {});
            restore_exception_handler();
            
            if ($previousHandler === null) {
                break;
            }
            
            restore_exception_handler();
        }
        
        // Restore all error handlers
        while (true) {
            $previousHandler = set_error_handler(function (int $errno, string $errstr, string $errfile, int $errline): bool {
                return true;
            });
            restore_error_handler();
            
            if ($previousHandler === null) {
                break;
            }
            
            restore_error_handler();
        }
    }
}
