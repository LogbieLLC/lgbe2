<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Config;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Register the config service if it doesn't exist
        if (!$this->app->bound('config')) {
            $this->app->singleton('config', function ($app) {
                return $app->make(\Illuminate\Config\Repository::class);
            });
        }
    }
}
