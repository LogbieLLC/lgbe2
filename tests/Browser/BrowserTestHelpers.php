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
        if ($firefoxBinary) {
            $firefoxOptions['binary'] = $firefoxBinary;
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
        $possiblePaths = [
            '/usr/bin/firefox-esr',
            '/usr/bin/firefox',
            '/snap/bin/firefox',
            '/Applications/Firefox.app/Contents/MacOS/firefox',
            'C:\\Program Files\\Mozilla Firefox\\firefox.exe',
            'C:\\Program Files (x86)\\Mozilla Firefox\\firefox.exe'
        ];

        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        // Try using which command on Unix-like systems
        if (PHP_OS_FAMILY !== 'Windows') {
            exec('which firefox-esr 2>/dev/null', $output);
            if (!empty($output)) {
                return $output[0];
            }
            
            exec('which firefox 2>/dev/null', $output);
            if (!empty($output)) {
                return $output[0];
            }
        }
        
        return null;
    }
}
