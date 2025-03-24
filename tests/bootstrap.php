<?php

use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Facade;

require __DIR__ . '/../vendor/autoload.php';

// Create a new application instance
$app = require __DIR__ . '/../bootstrap/app.php';

// Bootstrap the application
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Ensure config is bound
if (!$app->bound('config')) {
    $app->singleton('config', function () {
        return new Repository();
    });
}

// Set the application in the container
Container::setInstance($app);

// Set the facade application
Facade::setFacadeApplication($app);

// Run migrations for testing
$app->make(\Illuminate\Contracts\Console\Kernel::class)->call('migrate:fresh', [
    '--seed' => true,
    '--force' => true
]);

return $app;
