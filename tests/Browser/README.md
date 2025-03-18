# Browser Testing with Laravel Dusk and Firefox

This directory contains browser tests using Laravel Dusk with Firefox. These tests are designed to run on both Windows and Linux platforms.

## Setup

The tests use Firefox and GeckoDriver for browser automation. Make sure you have both installed on your system:

### Firefox Installation

- **Windows**: Download and install from [Mozilla Firefox](https://www.mozilla.org/firefox/new/)
- **Linux**: Install using your package manager, e.g., `sudo apt install firefox` or `sudo apt install firefox-esr`

### GeckoDriver

GeckoDriver is required for Firefox automation, but you don't need to install it manually. Our test scripts will automatically:

1. Check if GeckoDriver is already installed
2. If not found, download the appropriate version for your operating system and architecture
3. Install it either globally (if you have sudo access) or locally in the project directory
4. Clean up temporary files after installation

The scripts include multiple fallback methods to ensure successful installation:

#### Windows Fallbacks:
- First tries PowerShell's Invoke-WebRequest for downloading
- Falls back to curl if PowerShell fails
- Uses PowerShell's Expand-Archive for extraction
- Falls back to System.IO.Compression.FileSystem if the first method fails
- Falls back to tar command as a final extraction option
- Provides manual download instructions if all automated methods fail

#### Linux/macOS Fallbacks:
- Tries wget first, then curl for downloading
- Handles different architectures (x64, ARM64)
- Attempts global installation with sudo if available
- Falls back to local installation if sudo access is unavailable

This robust automatic installation works on:
- Windows (x64)
- macOS (Intel and Apple Silicon)
- Linux (x64 and ARM64)

## Running Tests

We provide scripts for running the tests on both Windows and Linux:

- **Windows**: Run `run-dusk-tests.bat` from the project root
- **Linux/macOS**: Run `./run-dusk-tests.sh` from the project root (make sure it's executable with `chmod +x run-dusk-tests.sh`)

## Exception Handler Management

The tests use a custom `ExceptionHandlerTrait` to properly manage exception handlers during test execution. This prevents the "Test code or tested code removed error handlers other than the default" warnings that can occur with browser tests.

### How It Works

1. Before each test, the original exception and error handlers are captured
2. After each test, these handlers are restored to their original state
3. This ensures that any changes to handlers during test execution are properly cleaned up

## Test Structure

Each test class should:

1. Extend `Tests\DuskTestCase`
2. Use the `DatabaseMigrations` trait if it needs a fresh database
3. Avoid manually calling `withoutExceptionHandling()` as it can cause handler issues

## Windows-Specific Considerations

When running tests on Windows, there are some platform-specific differences to be aware of:

1. **TTY Mode**: Windows doesn't support TTY mode, which is used by default in Laravel Dusk. Our Windows batch script automatically adds the `--no-tty` flag to prevent these warnings.

2. **Unix Commands**: Commands like `true` that are commonly used in Unix shell scripts don't exist in Windows. Our batch file uses Windows-specific alternatives and error handling.

3. **Process Management**: Windows uses different commands for process management (`taskkill` instead of `pkill`). The batch script handles this automatically.

4. **Path Separators**: Windows uses backslashes (`\`) for paths while Unix uses forward slashes (`/`). Laravel generally handles this automatically, but it's something to be aware of when writing tests.

## Handling Exception Handler Warnings

You may see warnings like `Test code or tested code removed error handlers other than its own` when running the tests. These warnings occur because PHPUnit is detecting changes to PHP's global error and exception handlers during test execution.

Our test suite includes an `ExceptionHandlerTrait` that properly manages these handlers to minimize these warnings. The trait:

1. Captures the original error and exception handlers at the start of each test
2. Restores these handlers at the end of each test
3. Uses a tracking flag to prevent duplicate handler operations

If you still see these warnings, they are generally harmless and don't affect test execution. They're a result of how Laravel Dusk and PHPUnit interact with PHP's error handling system.

## Connection Issues

If tests fail with connection errors like:

```
Reached error page: about:neterror?e=connectionFailure&u=http%3A//localhost%3A8000/
```

This means the tests couldn't connect to the Laravel development server. Our test scripts automatically:

1. Start a Laravel development server on port 8000 before running tests
2. Wait for the server to initialize
3. Stop the server after tests complete

If you still see these errors, check:
- If port 8000 is already in use by another application
- If your application has special requirements to run (environment variables, database setup, etc.)
- The server.log file for any startup errors

## Troubleshooting

If you encounter issues:

1. Make sure Firefox and GeckoDriver are properly installed and in your PATH
2. Check that the ports used by GeckoDriver (default: 4444) are not in use
3. Look for error messages in the console output
4. Check the screenshots in `tests/Browser/screenshots/` for visual clues
5. On Windows, if you see `'true' is not recognized as an internal or external command`, make sure you're using the latest version of the batch file which handles this issue
6. If tests fail with connection errors, check if the Laravel development server started correctly

## Screenshots and Reports

Test screenshots are saved to `tests/Browser/screenshots/` and an HTML report is generated at `tests/Browser/reports/report.html` after test execution.
