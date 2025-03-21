<?php

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(TestCase::class, RefreshDatabase::class)->in(__DIR__);

// Add helper to access artisan commands
function artisan($command, $parameters = [])
{
    return test()->artisan($command, $parameters);
}
