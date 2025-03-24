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
}
