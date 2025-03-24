@echo off
setlocal enabledelayedexpansion

echo.
echo ========================================
echo Running tests in order of operations
echo ========================================
echo.

:: Define colors for output
set "GREEN=[92m"
set "YELLOW=[93m"
set "RED=[91m"
set "BLUE=[94m"
set "RESET=[0m"

:: Define test steps
set "STEP[1]=PHP_CodeSniffer"
set "STEP[2]=PHPStan"
set "STEP[3]=ESLint"
set "STEP[4]=Pest"
set "STEP[5]=Jest"

:: Define commands for each step
set "CMD[1]=vendor\bin\phpcs"
set "CMD[2]=vendor\bin\phpstan analyse --memory-limit=512M"
set "CMD[3]=npm run lint"
set "CMD[4]=vendor\bin\pest"
set "CMD[5]=npm test"

:: Define descriptions for each step
set "DESC[1]=Enforces coding standards for PHP, ensuring the code is consistent, readable, and adheres to best practices."
set "DESC[2]=Performs static analysis on PHP code to uncover potential bugs, type errors, and logical inconsistencies."
set "DESC[3]=Lints JavaScript code within Vue.js components to enforce coding standards and flag common errors."
set "DESC[4]=Runs unit and integration tests for PHP code to verify that individual components and their interactions work correctly."
set "DESC[5]=Executes unit tests for JavaScript code in Vue.js components, ensuring they function as expected in isolation."

:: Initialize variables
set FAILED_STEPS=
set CURRENT_STEP=0
set TOTAL_STEPS=5
set ALL_PASSED=1

:: Parse command line arguments
set RUN_ALL=0
set SPECIFIC_STEP=0
set CONTINUE_ON_ERROR=0

if "%1"=="--all" (
    set RUN_ALL=1
) else if "%1"=="--continue" (
    set CONTINUE_ON_ERROR=1
) else if "%1"=="--step" (
    if "%2"=="" (
        echo %RED%Error: --step requires a number parameter.%RESET%
        goto :usage
    )
    set SPECIFIC_STEP=%2
    if !SPECIFIC_STEP! LSS 1 (
        echo %RED%Error: Step number must be between 1 and 5.%RESET%
        goto :usage
    )
    if !SPECIFIC_STEP! GTR 5 (
        echo %RED%Error: Step number must be between 1 and 5.%RESET%
        goto :usage
    )
) else if not "%1"=="" (
    goto :usage
)

:: Display usage information if needed
if "%1"=="--help" (
    goto :usage
)

:: Run tests
if %SPECIFIC_STEP% NEQ 0 (
    call :run_step %SPECIFIC_STEP%
) else (
    for /L %%i in (1,1,%TOTAL_STEPS%) do (
        set CURRENT_STEP=%%i
        
        call :run_step %%i
        
        if !ERRORLEVEL! NEQ 0 (
            set ALL_PASSED=0
            set FAILED_STEPS=!FAILED_STEPS! %%i
            
            if !CONTINUE_ON_ERROR! EQU 0 (
                if %%i LSS %TOTAL_STEPS% (
                    echo.
                    echo %RED%Test step %%i failed. Stopping test execution.%RESET%
                    echo %YELLOW%Use --continue flag to continue testing despite failures.%RESET%
                    goto :summary
                )
            )
        )
    )
)

:summary
echo.
echo ========================================
echo Test Execution Summary
echo ========================================

if !ALL_PASSED! EQU 1 (
    if %SPECIFIC_STEP% NEQ 0 (
        echo %GREEN%Step %SPECIFIC_STEP% (!STEP[%SPECIFIC_STEP%]!) passed successfully.%RESET%
    ) else (
        echo %GREEN%All test steps passed successfully!%RESET%
    )
) else (
    echo %RED%The following test steps failed:%RESET%
    for %%s in (!FAILED_STEPS!) do (
        echo %RED% - Step %%s: !STEP[%%s]!%RESET%
    )
)

echo.
echo ========================================
goto :eof

:run_step
set step=%1
echo.
echo %BLUE%Step %step%/%TOTAL_STEPS%: !STEP[%step%]!%RESET%
echo %BLUE%Description: !DESC[%step%]!%RESET%
echo.

call !CMD[%step%]!
if %ERRORLEVEL% NEQ 0 (
    echo %RED%Step %step% (!STEP[%step%]!) failed with error code %ERRORLEVEL%.%RESET%
    exit /b 1
) else (
    echo %GREEN%Step %step% (!STEP[%step%]!) completed successfully.%RESET%
    exit /b 0
)

:usage
echo.
echo Usage: run-tests.bat [options]
echo.
echo Options:
echo   --all         Run all tests
echo   --continue    Continue running tests even if a step fails
echo   --step N      Run only step N (1-5)
echo   --help        Display this help message
echo.
echo Test Steps:
echo   1. PHP_CodeSniffer - PHP coding standards
echo   2. PHPStan - PHP static analysis
echo   3. ESLint - JavaScript/Vue linting
echo   4. Pest - PHP unit/integration tests
echo   5. Jest - JavaScript unit tests
:: End-to-end browser tests removed
echo.
exit /b 1

endlocal
