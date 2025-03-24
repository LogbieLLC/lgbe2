<?php

namespace Tests;

trait ExceptionHandlerTrait
{
    /**
     * Clean up the test environment.
     */
    protected function tearDown(): void
    {
        // Restore error and exception handlers
        while (true) {
            // Keep restoring error handlers until we get back to the original
            $handler = set_error_handler(function () {});
            restore_error_handler();
            
            if ($handler === null) {
                break;
            }
        }
        
        // Restore exception handlers
        while (true) {
            // Keep restoring exception handlers until we get back to the original
            $handler = set_exception_handler(function () {});
            restore_exception_handler();
            
            if ($handler === null) {
                break;
            }
        }
        
        parent::tearDown();
    }
}
