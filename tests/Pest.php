<?php

// Only one extension for DuskTestCase is needed
pest()->extend(Tests\DuskTestCase::class)
    ->in('Browser');
