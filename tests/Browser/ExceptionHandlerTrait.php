<?php

namespace Tests\Browser;

/**
 * Trait for properly managing exception handlers in browser tests.
 * This trait helps prevent "Test code or tested code removed error handlers other than the default" warnings.
 */
trait ExceptionHandlerTrait
{
    /**
     * Flag to track if handlers have been backed up
     * 
     * @var bool
     */
    protected $handlersBackedUp = false;
    
    /**
     * Store original exception handlers to restore them later.
     *
     * @var array
     */
    protected $originalExceptionHandlers = [];
    
    /**
     * Store original error handlers to restore them later.
     *
     * @var array
     */
    protected $originalErrorHandlers = [];
    
    /**
     * Set up exception handler management.
     * Call this in setUp() method after parent::setUp().
     *
     * @return void
     */
    protected function setUpExceptionHandlers(): void
    {
        // Only backup handlers if not already done
        if (!$this->handlersBackedUp) {
            // Store original handlers
            $this->originalExceptionHandlers = $this->captureExceptionHandlers();
            $this->originalErrorHandlers = $this->captureErrorHandlers();
            $this->handlersBackedUp = true;
        }
    }
    
    /**
     * Clean up exception handler management.
     * Call this in tearDown() method before parent::tearDown().
     *
     * @return void
     */
    protected function tearDownExceptionHandlers(): void
    {
        if ($this->handlersBackedUp) {
            // Restore original handlers
            $this->restoreExceptionHandlers();
            $this->restoreErrorHandlers();
            $this->handlersBackedUp = false;
        }
    }
    
    /**
     * Capture current exception handlers.
     *
     * @return array
     */
    protected function captureExceptionHandlers(): array
    {
        $handlers = [];
        
        // Get the current exception handler
        $currentHandler = set_exception_handler(function(\Throwable $e) {
            // Default handler that just rethrows
            throw $e;
        });
        restore_exception_handler();
        
        if ($currentHandler !== null) {
            $handlers[] = $currentHandler;
        }
        
        return $handlers;
    }
    
    /**
     * Capture current error handlers.
     *
     * @return array
     */
    protected function captureErrorHandlers(): array
    {
        $handlers = [];
        
        // Get the current error handler
        $currentHandler = set_error_handler(function($severity, $message, $file, $line) {
            // Default handler that just returns false to let PHP handle it
            return false;
        });
        restore_error_handler();
        
        if ($currentHandler !== null) {
            $handlers[] = $currentHandler;
        }
        
        return $handlers;
    }
    
    /**
     * Restore original exception handlers.
     *
     * @return void
     */
    protected function restoreExceptionHandlers(): void
    {
        // First reset to default handler
        set_exception_handler(null);
        
        // Then apply original handlers in reverse order
        foreach (array_reverse($this->originalExceptionHandlers) as $handler) {
            set_exception_handler($handler);
        }
    }
    
    /**
     * Restore original error handlers.
     *
     * @return void
     */
    protected function restoreErrorHandlers(): void
    {
        // First reset to default handler
        set_error_handler(null);
        
        // Then apply original handlers in reverse order
        foreach (array_reverse($this->originalErrorHandlers) as $handler) {
            set_error_handler($handler);
        }
    }
}
