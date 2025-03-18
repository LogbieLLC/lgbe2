<?php

namespace Tests;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Laravel\Dusk\TestCase as BaseTestCase;
use PHPUnit\Framework\Attributes\BeforeClass;
use Tests\Browser\BrowserTestHelpers;

abstract class DuskTestCase extends BaseTestCase
{
    use CreatesApplication, BrowserTestHelpers;

    /**
     * Indicates whether exception handling is enabled.
     *
     * @var bool
     */
    protected $enablesExceptionHandling = true;

    /**
     * Prepare for Dusk test execution.
     */
    #[BeforeClass]
    public static function prepare()
    {
        if (!static::runningInSail()) {
            static::startFirefoxDriver();
        }
    }

    /**
     * Start the Firefox driver.
     *
     * @return void
     */
    protected static function startFirefoxDriver()
    {
        // Kill any existing Firefox processes
        if (PHP_OS_FAMILY === 'Linux') {
            exec('pkill -f geckodriver || true');
            exec('pkill -f firefox || true');
        } elseif (PHP_OS_FAMILY === 'Darwin') {
            exec('pkill -f geckodriver || true');
            exec('pkill -f firefox || true');
        } elseif (PHP_OS_FAMILY === 'Windows') {
            exec('taskkill /F /IM geckodriver.exe >nul 2>&1 || true');
            exec('taskkill /F /IM firefox.exe >nul 2>&1 || true');
        }
        
        // Wait for processes to fully terminate
        sleep(2);
        
        // Start GeckoDriver
        $port = 4444;
        
        // Set the driver URL environment variable
        putenv("DUSK_DRIVER_URL=http://localhost:{$port}");
        
        // Start GeckoDriver
        $command = "geckodriver --port {$port}";
        
        if (PHP_OS_FAMILY === 'Windows') {
            pclose(popen("start /B {$command} > NUL 2>&1", 'r'));
        } else {
            exec("{$command} > /dev/null 2>&1 &");
        }
        
        // Wait for GeckoDriver to start
        sleep(3);
    }

    /**
     * Create the RemoteWebDriver instance.
     *
     * @return \Facebook\WebDriver\Remote\RemoteWebDriver
     */
    protected function driver()
    {
        // Get the driver URL from environment or use default
        $driverUrl = env('DUSK_DRIVER_URL', 'http://localhost:4444');
        
        // Create the WebDriver instance with longer timeouts
        $driver = RemoteWebDriver::create(
            $driverUrl,
            $this->getFirefoxCapabilities(env('DUSK_HEADLESS', true)),
            60000, // Connection timeout in milliseconds
            60000  // Request timeout in milliseconds
        );
        
        return $driver;
    }
    
    /**
     * Setup before each test.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        if ($this->enablesExceptionHandling) {
            $this->withExceptionHandling();
        }
    }
    
    /**
     * Enable exception handling.
     *
     * @return $this
     */
    protected function withExceptionHandling()
    {
        $this->enablesExceptionHandling = true;
        
        $this->app->singleton(
            'Illuminate\Contracts\Debug\ExceptionHandler',
            'Illuminate\Foundation\Exceptions\Handler'
        );
        
        return $this;
    }
    
    /**
     * Disable exception handling for a test.
     *
     * @param  array  $except
     * @return $this
     */
    protected function withoutExceptionHandling(array $except = [])
    {
        $this->enablesExceptionHandling = false;
        
        $this->app->instance('Illuminate\Contracts\Debug\ExceptionHandler', new class($except) extends \Illuminate\Foundation\Exceptions\Handler {
            protected $except;
            
            public function __construct(array $except = [])
            {
                $this->except = $except;
            }
            
            public function report(\Throwable $e) {}
            
            public function render($request, \Throwable $e)
            {
                if ($this->shouldReport($e) && !$this->isExceptional($e)) {
                    throw $e;
                }
                
                return parent::render($request, $e);
            }
            
            protected function isExceptional(\Throwable $e)
            {
                return collect($this->except)->contains(function ($type) use ($e) {
                    return $e instanceof $type;
                });
            }
        });

        return $this;
    }
}
