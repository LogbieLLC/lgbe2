<?php

// Only one extension for DuskTestCase is needed
pest()->extend(Tests\DuskTestCase::class)
//  ->use(Illuminate\Foundation\Testing\DatabaseMigrations::class)
    ->in('Browser');
