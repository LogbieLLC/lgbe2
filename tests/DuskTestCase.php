<?php

namespace Tests;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Illuminate\Support\Collection;
use Laravel\Dusk\TestCase as BaseTestCase;
use PHPUnit\Framework\Attributes\AfterClass;
use PHPUnit\Framework\Attributes\BeforeClass;

abstract class DuskTestCase extends BaseTestCase
{
    /**
     * Create the RemoteWebDriver instance.
     *
     * @return \Facebook\WebDriver\Remote\RemoteWebDriver
     * @throws \Exception
     */
    protected function createWebDriver()
    {
        try {
            return retry(5, function () {
                try {
                    return $this->driver();
                } catch (\Facebook\WebDriver\Exception\SessionNotCreatedException $e) {
                    // If the error is about user data directory, try to clean up and retry
                    if (strpos($e->getMessage(), 'user data directory is already in use') !== false) {
                        // Clean up Chrome processes that might be holding the directory
                        $this->killChromeProcesses();
                        
                        // Wait a moment before retrying
                        usleep(250000); // 250ms
                    }
                    throw $e;
                }
            }, 50);
        } catch (\Facebook\WebDriver\Exception\SessionNotCreatedException $e) {
            throw new \Exception('Failed to create Chrome session: ' . $e->getMessage() . 
                "\nPlease ensure no other Chrome instances are running with the same user data directory.");
        }
    }

    /**
     * Kill Chrome processes that might be holding onto user data directories.
     *
     * @return void
     */
    protected function killChromeProcesses()
    {
        if (PHP_OS_FAMILY === 'Linux') {
            exec('pkill -f chrome');
        } elseif (PHP_OS_FAMILY === 'Darwin') {
            exec('pkill -f Google\ Chrome');
        } elseif (PHP_OS_FAMILY === 'Windows') {
            exec('taskkill /F /IM chrome.exe >nul 2>&1');
        }
    }
    /**
     * Prepare for Dusk test execution.
     */
    #[BeforeClass]
    public static function prepare(): void
    {
        if (! static::runningInSail()) {
            static::startChromeDriver(['--port=9515']);
        }
    }
    
    /**
     * Clean up after Dusk test execution.
     */
    #[AfterClass]
    public static function cleanup(): void
    {
        // Clean up Chrome user data directories
        $chromeDataDirs = glob(sys_get_temp_dir() . '/chrome_test_dirs/dusk_*');
        if (is_array($chromeDataDirs)) {
            foreach ($chromeDataDirs as $dir) {
                if (is_dir($dir)) {
                    static::recursiveRemoveDirectory($dir);
                }
            }
        }
    }
    
    /**
     * Recursively remove a directory and its contents.
     *
     * @param string $dir
     * @return void
     */
    protected static function recursiveRemoveDirectory($dir)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (is_dir($dir . "/" . $object)) {
                        static::recursiveRemoveDirectory($dir . "/" . $object);
                    } else {
                        unlink($dir . "/" . $object);
                    }
                }
            }
            rmdir($dir);
        }
    }

    /**
     * Create the RemoteWebDriver instance.
     */
    protected function driver(): RemoteWebDriver
    {
        // Create a unique temporary directory for Chrome user data
        $userDataDir = sys_get_temp_dir() . '/chrome_test_dirs/dusk_' . uniqid();
        if (!file_exists($userDataDir)) {
            mkdir($userDataDir, 0755, true);
        }

        $options = (new ChromeOptions)->addArguments(collect([
            $this->shouldStartMaximized() ? '--start-maximized' : '--window-size=1920,1080',
            '--disable-search-engine-choice-screen',
            '--disable-smooth-scrolling',
            '--user-data-dir=' . $userDataDir, // Add unique user data directory
        ])->unless($this->hasHeadlessDisabled(), function (Collection $items) {
            return $items->merge([
                '--disable-gpu',
                '--headless=new',
            ]);
        })->all());

        return RemoteWebDriver::create(
            $_ENV['DUSK_DRIVER_URL'] ?? env('DUSK_DRIVER_URL') ?? 'http://localhost:9515',
            DesiredCapabilities::chrome()->setCapability(
                ChromeOptions::CAPABILITY, $options
            )
        );
    }
}
