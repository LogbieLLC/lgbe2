<?php

namespace Tests\Browser;

/**
 * ChromeDriverService class to manage the lifecycle of ChromeDriver processes.
 * This class provides methods to start, stop, and verify ChromeDriver instances.
 */
class ChromeDriverService
{
    /**
     * The port that ChromeDriver should run on.
     *
     * @var int
     */
    protected $port;

    /**
     * The path to the ChromeDriver binary.
     *
     * @var string
     */
    protected $binary;

    /**
     * Create a new ChromeDriverService instance.
     *
     * @param int $port
     * @return void
     */
    public function __construct(int $port = 9515)
    {
        $this->port = $port;
        $this->setBinary();
    }

    /**
     * Set the ChromeDriver binary path based on the operating system.
     *
     * @return void
     */
    protected function setBinary(): void
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $this->binary = base_path('vendor/laravel/dusk/bin/chromedriver-win.exe');
        } elseif (PHP_OS_FAMILY === 'Darwin') {
            $this->binary = base_path('vendor/laravel/dusk/bin/chromedriver-mac');
        } else {
            $this->binary = base_path('vendor/laravel/dusk/bin/chromedriver-linux');
        }
    }

    /**
     * Start the ChromeDriver process.
     *
     * @return bool
     */
    public function start(): bool
    {
        // Kill any existing processes first
        $this->stop();

        echo "Starting ChromeDriver on port {$this->port}...\n";

        if (PHP_OS_FAMILY === 'Windows') {
            pclose(popen("start /B \"{$this->binary}\" --port={$this->port}", 'r'));
        } else {
            exec("{$this->binary} --port={$this->port} > /dev/null 2>&1 &");
        }

        // Wait for ChromeDriver to start
        sleep(3);

        return $this->isRunning();
    }

    /**
     * Stop any running ChromeDriver processes.
     *
     * @return void
     */
    public function stop(): void
    {
        echo "Stopping ChromeDriver processes...\n";

        if (PHP_OS_FAMILY === 'Linux') {
            exec('pkill -f chromedriver || true');
        } elseif (PHP_OS_FAMILY === 'Darwin') {
            exec('pkill -f chromedriver || true');
        } elseif (PHP_OS_FAMILY === 'Windows') {
            exec('taskkill /F /IM chromedriver.exe >nul 2>&1 || true');
        }

        // Wait for processes to terminate
        sleep(2);
    }

    /**
     * Check if ChromeDriver is running on the specified port.
     *
     * @return bool
     */
    public function isRunning(): bool
    {
        echo "Checking if ChromeDriver is running on port {$this->port}...\n";

        // Try to connect to ChromeDriver status endpoint
        $ch = curl_init("http://localhost:{$this->port}/status");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Check if we got a valid response
        if ($response && $httpCode === 200) {
            echo "ChromeDriver is running successfully on port {$this->port}.\n";
            return true;
        }

        echo "ChromeDriver is not running on port {$this->port}.\n";
        return false;
    }

    /**
     * Get the URL to connect to ChromeDriver.
     *
     * @return string
     */
    public function getDriverUrl(): string
    {
        return "http://localhost:{$this->port}";
    }
}
