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
        
        // Ensure config is bound for Laravel 11
        if (!$this->app->bound('config')) {
            $this->app->singleton('config', function () {
                return new Repository();
            });
        }
    }
}
