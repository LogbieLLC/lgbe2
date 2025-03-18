@echo off
setlocal enabledelayedexpansion

echo Starting Laravel Dusk End-to-End Tests with Firefox
echo ========================================

:: Kill any existing Firefox processes
echo Cleaning up existing Firefox processes...
taskkill /F /IM firefox.exe >nul 2>&1 || echo Firefox not running
taskkill /F /IM geckodriver.exe >nul 2>&1 || echo GeckoDriver not running

:: Wait for processes to fully terminate
timeout /t 2 /nobreak >nul

:: Define GeckoDriver port
set GECKODRIVER_PORT=4444
echo Using GeckoDriver port: %GECKODRIVER_PORT%

:: Check if GeckoDriver exists and download if not found
where geckodriver >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo GeckoDriver not found. Downloading and installing GeckoDriver...
    
    :: Create temp directory for download
    if not exist temp mkdir temp
    
    :: Set GeckoDriver version and download URL
    set GECKODRIVER_VERSION=v0.33.0
    set GECKODRIVER_URL=https://github.com/mozilla/geckodriver/releases/download/!GECKODRIVER_VERSION!/geckodriver-!GECKODRIVER_VERSION!-win64.zip
    
    :: Download GeckoDriver - try PowerShell first
    echo Downloading from: !GECKODRIVER_URL!
    powershell -Command "& { [Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12; try { Invoke-WebRequest -Uri '!GECKODRIVER_URL!' -OutFile 'temp\geckodriver.zip'; Write-Host 'Download successful with PowerShell.'; exit 0 } catch { Write-Host 'PowerShell download failed: ' + $_.Exception.Message; exit 1 } }"
    
    :: Check if PowerShell download was successful
    if %ERRORLEVEL% NEQ 0 (
        echo PowerShell download failed. Trying with curl...
        
        :: Try curl as a fallback (available in Windows 10 1803+)
        curl -L -o temp\geckodriver.zip !GECKODRIVER_URL!
        
        :: Check if the file exists and has content
        if exist temp\geckodriver.zip (
            for %%I in (temp\geckodriver.zip) do if %%~zI GTR 0 (
                echo Download successful with curl.
            ) else (
                echo Failed to download GeckoDriver with curl. File is empty.
                echo.
                echo Please download manually from:
                echo !GECKODRIVER_URL!
                echo Extract the zip file and place geckodriver.exe in the current directory.
                echo.
                rmdir /S /Q temp
                exit /b 1
            )
        ) else (
            echo Failed to download GeckoDriver with curl. File not found.
            echo.
            echo Please download manually from:
            echo !GECKODRIVER_URL!
            echo Extract the zip file and place geckodriver.exe in the current directory.
            echo.
            rmdir /S /Q temp
            exit /b 1
        )
    )
    
    :: Verify the file exists
    if not exist temp\geckodriver.zip (
        echo GeckoDriver zip file not found after download attempts.
        rmdir /S /Q temp
        exit /b 1
    )
    
    :: Extract GeckoDriver
    echo Extracting GeckoDriver...
    
    :: Try PowerShell extraction
    powershell -Command "Expand-Archive -Path 'temp\geckodriver.zip' -DestinationPath 'temp' -Force"
    
    :: Check if extraction was successful
    if not exist temp\geckodriver.exe (
        echo PowerShell extraction failed. Trying with tar command...
        
        :: Try using tar command (available in Windows 10 1803+)
        tar -xf temp\geckodriver.zip -C temp
        
        :: Check if tar extraction was successful
        if not exist temp\geckodriver.exe (
            echo All extraction methods failed.
            echo.
            echo Please download and extract manually from:
            echo !GECKODRIVER_URL!
            echo Place geckodriver.exe in the current directory.
            echo.
            rmdir /S /Q temp
            exit /b 1
        ) else (
            echo Extraction with tar successful.
        )
    ) else (
        echo Extraction successful.
    )
    
    :: Check if geckodriver.exe exists in temp directory
    if not exist temp\geckodriver.exe (
        echo GeckoDriver executable not found in extracted files.
        rmdir /S /Q temp
        exit /b 1
    )
    
    :: Move GeckoDriver to current directory
    echo Installing GeckoDriver...
    copy temp\geckodriver.exe . /Y
    
    :: Verify the copy was successful
    if not exist geckodriver.exe (
        echo Failed to copy GeckoDriver to current directory.
        rmdir /S /Q temp
        exit /b 1
    )
    
    :: Clean up
    echo Cleaning up...
    rmdir /S /Q temp
    
    echo GeckoDriver installed successfully.
)

:: Start GeckoDriver
echo Starting GeckoDriver...
start /B geckodriver --port %GECKODRIVER_PORT% >nul 2>&1

:: Wait for GeckoDriver to start
timeout /t 3 /nobreak >nul

:: Set the driver URL environment variable
set DUSK_DRIVER_URL=http://localhost:!GECKODRIVER_PORT!

:: Clear previous screenshots
echo Clearing previous screenshots...
if exist tests\Browser\screenshots\ (
    del /Q tests\Browser\screenshots\*
)

:: Find an available port for the Laravel development server
echo Finding available port for Laravel development server...
set SERVER_PORT=8000
:check_port
netstat -an | findstr ":%SERVER_PORT% " > nul
if %ERRORLEVEL% EQU 0 (
    :: Port is in use, try the next one
    set /a SERVER_PORT+=1
    if %SERVER_PORT% GTR 8020 (
        echo No available ports found between 8000 and 8020.
        echo Please free up a port or modify the script to use a different port range.
        exit /b 1
    )
    goto check_port
)
echo Using port %SERVER_PORT% for Laravel development server

:: Create custom phpunit.dusk.xml configuration for Firefox
echo Creating custom phpunit.dusk.xml configuration...
(
echo ^<?xml version="1.0" encoding="UTF-8"?^>
echo ^<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
echo         xsi:noNamespaceSchemaLocation="vendor/phpunit/phpunit/phpunit.xsd"
echo         bootstrap="tests/bootstrap.php"
echo         colors="true"
echo         backupGlobals="true"
echo         backupStaticAttributes="false"
echo         convertErrorsToExceptions="true"
echo         convertNoticesToExceptions="true"
echo         convertWarningsToExceptions="true"
echo         processIsolation="false"
echo         stopOnFailure="false"^>
echo     ^<testsuites^>
echo         ^<testsuite name="Browser"^>
echo             ^<directory^>tests/Browser^</directory^>
echo         ^</testsuite^>
echo     ^</testsuites^>
echo     ^<php^>
echo         ^<env name="APP_ENV" value="testing"/^>
echo         ^<env name="DUSK_DRIVER_URL" value="http://localhost:!GECKODRIVER_PORT!"/^>
echo         ^<env name="DUSK_WINDOWS_MODE" value="true"/^>
echo         ^<env name="APP_URL" value="http://localhost:%SERVER_PORT%"/^>
echo     ^</php^>
echo ^</phpunit^>
) > phpunit.dusk.xml

:: Start Laravel development server in the background
echo Starting Laravel development server...
start /B cmd /c "php artisan serve --port=%SERVER_PORT%" > server.log 2>&1
echo Waiting for server to start...
timeout /t 5 /nobreak > nul

:: Update the test environment to use the correct port
set APP_URL=http://localhost:%SERVER_PORT%

:: Run the tests with custom configuration
echo Running tests...
:: Set Windows-specific environment variables to handle TTY mode and other Unix-specific features
set DUSK_WINDOWS_MODE=true

:: Add current directory to PATH so our true.bat will be found
set PATH=%CD%;%PATH%

:: Run Dusk tests with Windows-specific settings
call php artisan dusk --configuration=phpunit.dusk.xml

:: Stop the Laravel development server
echo Stopping Laravel development server...
for /f "tokens=5" %%a in ('netstat -ano ^| findstr :%SERVER_PORT%') do (
    taskkill /F /PID %%a >nul 2>&1 || echo No server process found
)

:: Check if tests passed
if %ERRORLEVEL% EQU 0 (
    echo All tests passed!
) else (
    echo Some tests failed. Check the output above for details.
    echo Screenshots of failed tests are available in tests\Browser\screenshots\
)

:: Cleanup GeckoDriver after tests
echo Cleaning up GeckoDriver process...
taskkill /F /IM geckodriver.exe >nul 2>&1 || echo GeckoDriver not running
taskkill /F /IM firefox.exe >nul 2>&1 || echo Firefox not running

:: Generate a simple HTML report
echo Generating test report...

:: Create report directory if it doesn't exist
if not exist tests\Browser\reports\ (
    mkdir tests\Browser\reports\
)

:: Get current date and time using PowerShell
for /f "delims=" %%a in ('powershell -Command "Get-Date -Format 'yyyy-MM-dd HH:mm:ss'"') do set "DATE=%%a"

:: Create HTML report
(
echo ^<!DOCTYPE html^>
echo ^<html lang="en"^>
echo ^<head^>
echo     ^<meta charset="UTF-8"^>
echo     ^<meta name="viewport" content="width=device-width, initial-scale=1.0"^>
echo     ^<title^>Dusk Test Report^</title^>
echo     ^<style^>
echo         body {
echo             font-family: Arial, sans-serif;
echo             line-height: 1.6;
echo             margin: 0;
echo             padding: 20px;
echo             color: #333;
echo         }
echo         h1, h2 {
echo             color: #2c3e50;
echo         }
echo         .container {
echo             max-width: 1200px;
echo             margin: 0 auto;
echo         }
echo         .screenshot {
echo             margin-bottom: 20px;
echo             border: 1px solid #ddd;
echo             padding: 10px;
echo             border-radius: 4px;
echo         }
echo         .screenshot img {
echo             max-width: 100%%;
echo             height: auto;
echo             display: block;
echo             margin-top: 10px;
echo         }
echo         .timestamp {
echo             color: #7f8c8d;
echo             font-size: 0.9em;
echo         }
echo     ^</style^>
echo ^</head^>
echo ^<body^>
echo     ^<div class="container"^>
echo         ^<h1^>Laravel Dusk Test Report^</h1^>
echo         ^<p class="timestamp"^>Generated on: %DATE%^</p^>
echo         
echo         ^<h2^>Test Screenshots^</h2^>
echo         ^<div class="screenshots"^>
) > tests\Browser\reports\report.html

:: Add screenshots to the report
for %%f in (tests\Browser\screenshots\*.png) do (
    echo             ^<div class="screenshot"^> >> tests\Browser\reports\report.html
    echo                 ^<h3^>%%~nxf^</h3^> >> tests\Browser\reports\report.html
    echo                 ^<img src="../screenshots/%%~nxf" alt="%%~nxf"^> >> tests\Browser\reports\report.html
    echo             ^</div^> >> tests\Browser\reports\report.html
)

:: Close the HTML file
(
echo         ^</div^>
echo     ^</div^>
echo ^</body^>
echo ^</html^>
) >> tests\Browser\reports\report.html

echo Test report generated at tests\Browser\reports\report.html
echo ========================================

endlocal
