<?php

// Only one extension for DuskTestCase is needed
pest()->extend(Tests\DuskTestCase::class)
    ->in('Browser');

uses(
    Tests\TestCase::class,
    Illuminate\Foundation\Testing\RefreshDatabase::class
)->in('Feature');

// Helper functions are not needed as we're using $this in the test closures
