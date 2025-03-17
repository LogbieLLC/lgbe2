@echo off
setlocal enabledelayedexpansion

echo Starting Laravel Dusk End-to-End Tests
echo ========================================

:: Ensure Chrome driver is running
echo Starting Chrome driver...
call php artisan dusk:chrome-driver --detect
call php artisan dusk:chrome-driver

:: Clear previous screenshots
echo Clearing previous screenshots...
if exist tests\Browser\screenshots\ (
    del /Q tests\Browser\screenshots\*
)

:: Run the tests
echo Running tests...
call php artisan dusk

:: Check if tests passed
if %ERRORLEVEL% EQU 0 (
    echo All tests passed!
) else (
    echo Some tests failed. Check the output above for details.
    echo Screenshots of failed tests are available in tests\Browser\screenshots\
)

:: Generate a simple HTML report
echo Generating test report...

:: Create report directory if it doesn't exist
if not exist tests\Browser\reports\ (
    mkdir tests\Browser\reports\
)

:: Get current date and time
for /f "tokens=2 delims==" %%a in ('wmic OS Get localdatetime /value') do set "dt=%%a"
set "YYYY=%dt:~0,4%"
set "MM=%dt:~4,2%"
set "DD=%dt:~6,2%"
set "HH=%dt:~8,2%"
set "Min=%dt:~10,2%"
set "Sec=%dt:~12,2%"
set "DATE=%YYYY%-%MM%-%DD% %HH%:%Min%:%Sec%"

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
