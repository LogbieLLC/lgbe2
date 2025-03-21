<?php

// Only one extension for DuskTestCase is needed
pest()->extend(Tests\DuskTestCase::class)
    ->in('Browser');

uses(Tests\TestCase::class)->in('Feature');

function artisan($command, $parameters = [])
{
    return test()->artisan($command, $parameters);
}
