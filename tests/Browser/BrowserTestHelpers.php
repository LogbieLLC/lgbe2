<?php

namespace Tests\Browser;

use Facebook\WebDriver\Firefox\FirefoxOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;

trait BrowserTestHelpers
{
    use ExceptionHandlerTrait;

    /**
     * Get Firefox WebDriver capabilities with proper configuration.
     *
     * @param  bool  $headless
     * @return \Facebook\WebDriver\Remote\DesiredCapabilities
     */
    protected function getFirefoxCapabilities($headless = true)
    {
        // Create Firefox options
        $options = new FirefoxOptions();

        // Add arguments
        $options->addArguments(['--window-size=1280,720']);
        if ($headless) {
            $options->addArguments(['--headless']);
        }

        // Find Firefox binary
        $firefoxBinary = $this->findFirefoxBinary();

        // Get Firefox options as array and set binary path directly
        $firefoxOptions = $options->toArray();

        // Set binary path if found
        if ($firefoxBinary) {
            // Set binary path in options array for Firefox
            $firefoxOptions['binary'] = $firefoxBinary;

            // Log the Firefox binary path for debugging
            error_log("Using Firefox binary: {$firefoxBinary}");
        } else {
            error_log("Firefox binary not found!");
        }

        // Set preferences for better test stability
        $options->setPreference('browser.startup.homepage', 'about:blank');
        $options->setPreference('browser.startup.page', 0);
        $options->setPreference('browser.cache.disk.enable', false);
        $options->setPreference('browser.cache.memory.enable', false);
        $options->setPreference('browser.cache.offline.enable', false);
        $options->setPreference('network.http.use-cache', false);
        $options->setPreference('dom.disable_beforeunload', true);
        $options->setPreference('dom.webnotifications.enabled', false);

        // Create capabilities
        $capabilities = DesiredCapabilities::firefox();
        $capabilities->setCapability('moz:firefoxOptions', $firefoxOptions);

        // Set page load strategy to normal for better compatibility
        $capabilities->setCapability('pageLoadStrategy', 'normal');

        return $capabilities;
    }

    /**
     * Find the Firefox binary path.
     *
     * @return string|null
     */
    protected function findFirefoxBinary()
    {
        // Check for environment variable first (used in CI)
        $envBinary = getenv('FIREFOX_BINARY_PATH');
        if ($envBinary && file_exists($envBinary)) {
            error_log("Using Firefox from environment variable: {$envBinary}");
            return $envBinary;
        }

        // Prioritize Firefox ESR which is more stable for testing
        if (file_exists('/usr/bin/firefox-esr')) {
            error_log("Using Firefox ESR at: /usr/bin/firefox-esr");
            return '/usr/bin/firefox-esr';
        }

        // Use the wrapper script we created in run-dusk-tests.sh
        $wrapperScript = '/tmp/firefox-wrapper.sh';
        if (file_exists($wrapperScript) && is_executable($wrapperScript)) {
            error_log("Using Firefox wrapper script at: " . $wrapperScript);
            return $wrapperScript;
        }

        // Check for other possible Firefox installations
        $possiblePaths = [
            '/usr/bin/firefox-esr',
            '/usr/local/bin/firefox-esr',
            '/opt/firefox/firefox',
            '/usr/bin/firefox',
            '/usr/local/bin/firefox',
            '/snap/bin/firefox',
            '/snap/firefox/current/usr/lib/firefox/firefox',
            '/usr/lib/firefox/firefox',
            '/Applications/Firefox.app/Contents/MacOS/firefox',
            'C:\\Program Files\\Mozilla Firefox\\firefox.exe',
            'C:\\Program Files (x86)\\Mozilla Firefox\\firefox.exe'
        ];

        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                error_log("Found Firefox at: " . $path);
                return $path;
            }
        }

        // Try using which command on Unix-like systems
        if (PHP_OS_FAMILY !== 'Windows') {
            exec('which firefox-esr 2>/dev/null', $output);
            if (!empty($output)) {
                error_log("Found Firefox-esr at: " . $output[0]);
                return $output[0];
            }

            exec('which firefox 2>/dev/null', $output);
            if (!empty($output)) {
                error_log("Found Firefox at: " . $output[0]);
                return $output[0];
            }
        }

        error_log("No Firefox binary found!");
        return null;
    }
}
