<?php

namespace Tests;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Illuminate\Support\Collection;
use Laravel\Dusk\TestCase as BaseTestCase;
use PHPUnit\Framework\Attributes\AfterClass;
use PHPUnit\Framework\Attributes\BeforeClass;
use Database\Seeders\DuskTestSeeder;
use Tests\Browser\BrowserTestHelpers;

abstract class DuskTestCase extends BaseTestCase
{
    use BrowserTestHelpers;
    
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // Refresh the database and run the seeder if DatabaseMigrations trait is used
        if (isset($this->refreshDatabase) && $this->refreshDatabase) {
            $this->artisan('migrate:fresh');
            $this->artisan('db:seed', ['--class' => DuskTestSeeder::class]);
        }
    }
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
        // This is critical to prevent "user data directory is already in use" errors
        // especially in CI environments or when tests are run in parallel
        $tempDir = sys_get_temp_dir();
        $userDataDir = $tempDir . '/chrome_test_dirs/dusk_' . uniqid();
        
        // Ensure the directory exists and is writable
        if (!file_exists($userDataDir)) {
            mkdir($userDataDir, 0755, true);
        }
        
        // Log the user data directory for debugging
        echo "Using Chrome user data directory: $userDataDir\n";

        $options = (new ChromeOptions);
        
        // Set Chrome binary path for Windows
        if (PHP_OS_FAMILY === 'Windows') {
            // Common Chrome installation paths on Windows
            $possiblePaths = [
                'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
                'C:\\Program Files (x86)\\Google\\Chrome\\Application\\chrome.exe',
                'C:\\Users\\' . get_current_user() . '\\AppData\\Local\\Google\\Chrome\\Application\\chrome.exe',
                'C:\\Program Files\\Microsoft\\Edge\\Application\\msedge.exe', // Try Microsoft Edge as a fallback
            ];
            
            $foundBinary = false;
            foreach ($possiblePaths as $path) {
                if (file_exists($path)) {
                    echo "Found browser at: $path\n";
                    $options->setBinary($path);
                    $foundBinary = true;
                    break;
                }
            }
            
            if (!$foundBinary) {
                echo "Could not find Chrome or Edge binary in common locations.\n";
                echo "Please install Chrome or specify the path manually in tests/DuskTestCase.php.\n";
                
                // Try to find Chrome using 'where' command
                $output = [];
                exec('where chrome.exe', $output);
                if (!empty($output)) {
                    echo "Found Chrome using 'where' command at: {$output[0]}\n";
                    $options->setBinary($output[0]);
                } else {
                    echo "Could not find Chrome using 'where' command.\n";
                }
            }
        } elseif (PHP_OS_FAMILY === 'Linux') {
            // Try to find Chrome on Linux
            $output = [];
            exec('which google-chrome', $output);
            if (!empty($output)) {
                echo "Found Chrome on Linux at: {$output[0]}\n";
                $options->setBinary($output[0]);
            }
        } elseif (PHP_OS_FAMILY === 'Darwin') {
            // Try to find Chrome on macOS
            $macOSChromePaths = [
                '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
                $tempDir . '/Google Chrome.app/Contents/MacOS/Google Chrome'
            ];
            
            foreach ($macOSChromePaths as $path) {
                if (file_exists($path)) {
                    echo "Found Chrome on macOS at: $path\n";
                    $options->setBinary($path);
                    break;
                }
            }
        }
        
        // Add Chrome arguments
        // The --user-data-dir argument is critical to prevent session conflicts
        $chromeArguments = collect([
            $this->shouldStartMaximized() ? '--start-maximized' : '--window-size=1920,1080',
            '--disable-search-engine-choice-screen',
            '--disable-smooth-scrolling',
            '--user-data-dir=' . $userDataDir,
            '--no-sandbox', // Often needed in CI environments
            '--disable-dev-shm-usage', // Helps with memory issues in CI
        ]);
        
        // Add headless arguments if not disabled
        if (!$this->hasHeadlessDisabled()) {
            $chromeArguments = $chromeArguments->merge([
                '--disable-gpu',
                '--headless=new',
            ]);
        }
        
        $options->addArguments($chromeArguments->all());

        return RemoteWebDriver::create(
            $_ENV['DUSK_DRIVER_URL'] ?? env('DUSK_DRIVER_URL') ?? 'http://localhost:9515',
            DesiredCapabilities::chrome()->setCapability(
                ChromeOptions::CAPABILITY, $options
            )
        );
    }
}
