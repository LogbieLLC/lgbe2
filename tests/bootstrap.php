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

// Create SQLite database file if needed
$databasePath = dirname(__DIR__) . '/database/database.sqlite';
$databaseDir = dirname($databasePath);

if (!file_exists($databaseDir)) {
    mkdir($databaseDir, 0777, true);
}

if (!file_exists($databasePath)) {
    touch($databasePath);
}

// Run migrations for testing
$app->make(\Illuminate\Contracts\Console\Kernel::class)->call('migrate:fresh', [
    '--seed' => true,
    '--force' => true
]);

// Register shutdown function to restore error and exception handlers
register_shutdown_function(function () {
    // Restore all exception handlers
    while (true) {
        $previousHandler = set_exception_handler(function () {});
        restore_exception_handler();
        
        if ($previousHandler === null) {
            break;
        }
        
        restore_exception_handler();
    }
    
    // Restore all error handlers
    while (true) {
        $previousHandler = set_error_handler(function () {});
        restore_error_handler();
        
        if ($previousHandler === null) {
            break;
        }
        
        restore_error_handler();
    }
});

return $app;
