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

        // Mock Vite to prevent ViteManifestNotFoundException
        $this->withoutVite();

        // Run migrations for in-memory database
        if (config('database.default') === 'sqlite' && config('database.connections.sqlite.database') === ':memory:') {
            $this->artisan('migrate:fresh');
        }

        // Ensure config is bound for Laravel 11
        if (!$this->app->bound('config')) {
            $this->app->singleton('config', function () {
                return new Repository();
            });
        }
    }

    /**
     * Clean up after each test.
     */
    protected function tearDown(): void
    {
        // Clear application cache
        if ($this->app) {
            $this->artisan('cache:clear');
        }
        
        parent::tearDown();
    }
}
