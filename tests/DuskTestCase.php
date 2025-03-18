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
            // Kill any existing ChromeDriver and Chrome processes
            static::killExistingProcesses();
            
            // Define the ChromeDriver port
            $port = 9515;
            
            // Start ChromeDriver with explicit port
            static::startChromeDriver(['--port=' . $port]);
            
            // Wait for ChromeDriver to be ready
            sleep(3);
            
            // Verify ChromeDriver is running
            if (!static::verifyDriverIsRunning($port)) {
                echo "Warning: ChromeDriver may not be running properly. Attempting to restart...\n";
                
                // Try one more time with a different approach
                static::killExistingProcesses();
                
                // Start ChromeDriver directly
                static::startChromeDriverManually($port);
                
                // Wait and verify again
                sleep(3);
                if (!static::verifyDriverIsRunning($port)) {
                    echo "Error: ChromeDriver failed to start after multiple attempts.\n";
                } else {
                    echo "ChromeDriver started successfully on second attempt.\n";
                }
            } else {
                echo "ChromeDriver started successfully.\n";
            }
            
            // Set the driver URL environment variable
            putenv("DUSK_DRIVER_URL=http://localhost:{$port}");
        }
    }
    
    /**
     * Kill existing ChromeDriver and Chrome processes.
     */
    protected static function killExistingProcesses(): void
    {
        echo "Cleaning up existing Chrome processes...\n";
        
        if (PHP_OS_FAMILY === 'Linux') {
            exec('pkill -f chromedriver || true');
            exec('pkill -f chrome || true');
        } elseif (PHP_OS_FAMILY === 'Darwin') {
            exec('pkill -f chromedriver || true');
            exec('pkill -f "Google Chrome" || true');
        } elseif (PHP_OS_FAMILY === 'Windows') {
            exec('taskkill /F /IM chromedriver.exe >nul 2>&1 || true');
            exec('taskkill /F /IM chrome.exe >nul 2>&1 || true');
        }
        
        // Wait for processes to fully terminate
        sleep(2);
    }
    
    /**
     * Start ChromeDriver manually using direct execution.
     */
    protected static function startChromeDriverManually(int $port): void
    {
        echo "Starting ChromeDriver manually on port {$port}...\n";
        
        if (PHP_OS_FAMILY === 'Windows') {
            pclose(popen("start /B vendor\\laravel\\dusk\\bin\\chromedriver-win.exe --port={$port}", 'r'));
        } elseif (PHP_OS_FAMILY === 'Darwin') {
            exec("vendor/laravel/dusk/bin/chromedriver-mac --port={$port} > /dev/null 2>&1 &");
        } else {
            exec("vendor/laravel/dusk/bin/chromedriver-linux --port={$port} > /dev/null 2>&1 &");
        }
    }
    
    /**
     * Verify that ChromeDriver is running on the specified port.
     */
    protected static function verifyDriverIsRunning(int $port): bool
    {
        echo "Verifying ChromeDriver is running on port {$port}...\n";
        
        // Try to connect to ChromeDriver status endpoint
        $ch = curl_init("http://localhost:{$port}/status");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        // Check if we got a valid response
        if ($response && $httpCode === 200) {
            echo "ChromeDriver is running successfully on port {$port}.\n";
            return true;
        }
        
        // Check if the process is running using OS-specific commands
        if (PHP_OS_FAMILY === 'Linux') {
            exec("pgrep -f 'chromedriver.*--port={$port}'", $output);
            return !empty($output);
        }
        
        return false;
    }
    
    /**
     * Clean up after Dusk test execution.
     */
    #[AfterClass]
    public static function cleanup(): void
    {
        // Kill any remaining Chrome and ChromeDriver processes
        static::killExistingProcesses();
        
        // Clean up Chrome user data directories
        $chromeDataDirs = glob(sys_get_temp_dir() . '/chrome_test_dirs/dusk_*');
        if (is_array($chromeDataDirs)) {
            foreach ($chromeDataDirs as $dir) {
                if (is_dir($dir)) {
                    static::recursiveRemoveDirectory($dir);
                }
            }
        }
        
        echo "Cleanup completed successfully.\n";
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
        
        // Verify ChromeDriver is still running before attempting to connect
        $driverUrl = $_ENV['DUSK_DRIVER_URL'] ?? env('DUSK_DRIVER_URL') ?? 'http://localhost:9515';
        $port = parse_url($driverUrl, PHP_URL_PORT) ?: 9515;
        
        if (!static::verifyDriverIsRunning($port)) {
            echo "ChromeDriver not running before test. Attempting to restart...\n";
            static::killExistingProcesses();
            static::startChromeDriverManually($port);
            sleep(3);
            
            if (!static::verifyDriverIsRunning($port)) {
                throw new \Exception("Failed to start ChromeDriver on port {$port}. Tests cannot continue.");
            }
        }

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

        // Get the driver URL from environment or use default
        $driverUrl = $_ENV['DUSK_DRIVER_URL'] ?? env('DUSK_DRIVER_URL') ?? 'http://localhost:9515';
        echo "Connecting to ChromeDriver at: {$driverUrl}\n";
        
        try {
            return RemoteWebDriver::create(
                $driverUrl,
                DesiredCapabilities::chrome()->setCapability(
                    ChromeOptions::CAPABILITY, $options
                ),
                60000, // Connection timeout in milliseconds (60 seconds)
                60000  // Request timeout in milliseconds (60 seconds)
            );
        } catch (\Exception $e) {
            echo "Error connecting to ChromeDriver: " . $e->getMessage() . "\n";
            
            // Try to restart ChromeDriver one more time
            $port = parse_url($driverUrl, PHP_URL_PORT) ?: 9515;
            echo "Attempting to restart ChromeDriver on port {$port}...\n";
            
            static::killExistingProcesses();
            static::startChromeDriverManually($port);
            sleep(5); // Give it more time to start
            
            // Try again with a fresh connection
            return RemoteWebDriver::create(
                $driverUrl,
                DesiredCapabilities::chrome()->setCapability(
                    ChromeOptions::CAPABILITY, $options
                ),
                60000, // Connection timeout in milliseconds (60 seconds)
                60000  // Request timeout in milliseconds (60 seconds)
            );
        }
    }
}
