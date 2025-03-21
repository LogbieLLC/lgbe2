#!/usr/bin/env python3
"""
Cross-platform Laravel Dusk test runner for LGBE2.

This script handles:
1. Creating and using a virtual environment
2. Installing required dependencies
3. Cleaning up existing Firefox/GeckoDriver processes
4. Installing GeckoDriver if needed
5. Starting GeckoDriver and Laravel server
6. Running Dusk tests
7. Generating test reports
8. Cleaning up resources
"""

import os
import sys
import platform
import subprocess
import time
import shutil
import tempfile
import socket
import urllib.request
import urllib.error
import zipfile
import tarfile
import glob
from pathlib import Path
from datetime import datetime


# ANSI colors for terminal output
class Colors:
    GREEN = '\033[0;32m'
    RED = '\033[0;31m'
    YELLOW = '\033[0;33m'
    NC = '\033[0m'  # No Color


# Clear colors for Windows if not using ANSI-compatible terminal
if platform.system() == 'Windows' and not os.environ.get('TERM') == 'xterm':
    for attr in dir(Colors):
        if not attr.startswith('__'):
            setattr(Colors, attr, '')


def print_colored(text, color):
    """Print colored text to the console."""
    print(f"{color}{text}{Colors.NC}")


def print_header(text):
    """Print a section header."""
    print_colored(f"\n{text}", Colors.YELLOW)
    print("=" * 40)


def run_command(cmd, shell=True, cwd=None, env=None, check=False):
    """Run a command and return the process."""
    try:
        return subprocess.run(
            cmd,
            shell=shell,
            cwd=cwd,
            env=env,
            check=check,
            stdout=subprocess.PIPE,
            stderr=subprocess.PIPE,
            text=True
        )
    except subprocess.CalledProcessError as e:
        print_colored(f"Command failed: {cmd}", Colors.RED)
        print(f"Output: {e.stdout}")
        print(f"Error: {e.stderr}")
        if check:
            raise
        return e


def kill_processes():
    """Kill existing Firefox and GeckoDriver processes."""
    print_header("Cleaning up existing Firefox and GeckoDriver processes")
    
    if platform.system() == 'Windows':
        run_command("taskkill /F /IM firefox.exe >nul 2>&1", check=False)
        run_command("taskkill /F /IM geckodriver.exe >nul 2>&1", check=False)
    else:  # Linux or macOS
        run_command("pkill -f firefox || true", check=False)
        run_command("pkill -f geckodriver || true", check=False)
    
    # Wait for processes to fully terminate
    time.sleep(2)
    print_colored("Process cleanup completed", Colors.GREEN)


def setup_virtual_environment():
    """Set up Python virtual environment and install dependencies."""
    print_header("Setting up Python virtual environment")
    
    venv_dir = Path("venv")
    if not venv_dir.exists():
        # Create virtual environment
        print_colored("Creating virtual environment...", Colors.YELLOW)
        run_command(f"{sys.executable} -m venv venv", check=True)
    
    # Determine the Python executable in the virtual environment
    if platform.system() == 'Windows':
        python_executable = venv_dir / "Scripts" / "python.exe"
    else:
        python_executable = venv_dir / "bin" / "python"
    
    # Upgrade pip and install dependencies
    print_colored("Installing dependencies...", Colors.YELLOW)
    run_command(f"{python_executable} -m pip install --upgrade pip", check=True)
    run_command(f"{python_executable} -m pip install requests", check=True)
    
    return python_executable


def check_geckodriver():
    """Check if GeckoDriver is installed, download and install if not."""
    print_header("Verifying GeckoDriver installation")
    
    # Check if GeckoDriver is in PATH
    result = run_command("geckodriver --version", check=False)
    if result.returncode == 0:
        print_colored("GeckoDriver is already installed.", Colors.GREEN)
        return True
    
    print_colored("GeckoDriver not found. Installing GeckoDriver...", Colors.YELLOW)
    
    # Set GeckoDriver version and download URL
    geckodriver_version = "v0.33.0"
    temp_dir = tempfile.mkdtemp()
    
    try:
        # Determine appropriate archive based on OS and architecture
        system = platform.system().lower()
        machine = platform.machine().lower()
        
        if system == 'windows':
            archive_name = f"geckodriver-{geckodriver_version}-win64.zip"
        elif system == 'darwin':  # macOS
            if machine == 'arm64':  # Apple Silicon
                archive_name = f"geckodriver-{geckodriver_version}-macos-aarch64.tar.gz"
            else:  # Intel Mac
                archive_name = f"geckodriver-{geckodriver_version}-macos.tar.gz"
        else:  # Linux
            if machine == 'aarch64' or machine == 'arm64':
                archive_name = f"geckodriver-{geckodriver_version}-linux-aarch64.tar.gz"
            else:
                archive_name = f"geckodriver-{geckodriver_version}-linux64.tar.gz"
        
        download_url = f"https://github.com/mozilla/geckodriver/releases/download/{geckodriver_version}/{archive_name}"
        archive_path = os.path.join(temp_dir, archive_name)
        
        # Download GeckoDriver
        print_colored(f"Downloading from: {download_url}", Colors.YELLOW)
        try:
            urllib.request.urlretrieve(download_url, archive_path)
        except urllib.error.URLError as e:
            print_colored(f"Download failed: {e}", Colors.RED)
            print_colored("Please download GeckoDriver manually from:", Colors.YELLOW)
            print(download_url)
            return False
        
        # Extract GeckoDriver
        print_colored("Extracting GeckoDriver...", Colors.YELLOW)
        geckodriver_path = None
        
        if archive_name.endswith('.zip'):
            with zipfile.ZipFile(archive_path, 'r') as zip_ref:
                zip_ref.extractall(temp_dir)
        else:  # tar.gz
            with tarfile.open(archive_path, 'r:gz') as tar_ref:
                tar_ref.extractall(temp_dir)
        
        # Find geckodriver executable
        if platform.system() == 'Windows':
            geckodriver_path = os.path.join(temp_dir, "geckodriver.exe")
        else:
            geckodriver_path = os.path.join(temp_dir, "geckodriver")
        
        if not os.path.exists(geckodriver_path):
            print_colored("GeckoDriver executable not found in extracted files.", Colors.RED)
            return False
        
        # Make executable on Unix systems
        if platform.system() != 'Windows':
            os.chmod(geckodriver_path, 0o755)
        
        # Move to current directory
        shutil.copy(geckodriver_path, os.getcwd())
        print_colored("GeckoDriver installed successfully.", Colors.GREEN)
        
        return True
        
    finally:
        # Clean up temp directory
        shutil.rmtree(temp_dir, ignore_errors=True)


def start_geckodriver(port=4444):
    """Start GeckoDriver on the specified port."""
    print_header(f"Starting GeckoDriver on port {port}")
    
    # Start GeckoDriver using the local executable
    geckodriver_path = os.path.join(os.getcwd(), "geckodriver")
    if platform.system() == 'Windows':
        geckodriver_path += ".exe"
    
    if platform.system() == 'Windows':
        cmd = f"start /B {geckodriver_path} --port {port}"
        subprocess.Popen(cmd, shell=True, stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL)
    else:
        cmd = f"{geckodriver_path} --port {port} > /tmp/geckodriver.log 2>&1 &"
        subprocess.Popen(cmd, shell=True, stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL)
    
    print_colored("Waiting for GeckoDriver to start...", Colors.YELLOW)
    time.sleep(3)
    
    # Verify GeckoDriver is running
    try:
        with urllib.request.urlopen(f"http://localhost:{port}/status") as response:
            if response.status == 200:
                print_colored(f"GeckoDriver is running on port {port}", Colors.GREEN)
                return True
    except:
        print_colored("GeckoDriver failed to start.", Colors.RED)
        if platform.system() != 'Windows':
            try:
                with open("/tmp/geckodriver.log", "r") as f:
                    print(f.read())
            except:
                pass
        return False
    
    return True


def find_available_port(start_port=8000, end_port=8020):
    """Find an available port in the given range."""
    for port in range(start_port, end_port + 1):
        with socket.socket(socket.AF_INET, socket.SOCK_STREAM) as s:
            if s.connect_ex(('localhost', port)) != 0:
                return port
    
    print_colored(f"No available ports found between {start_port} and {end_port}.", Colors.RED)
    print_colored("Please free up a port or modify the script to use a different port range.", Colors.RED)
    return None


def create_phpunit_config(server_port, geckodriver_port):
    """Create custom phpunit.dusk.xml configuration."""
    print_header("Creating custom phpunit.dusk.xml configuration")
    
    config_content = f"""<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="vendor/phpunit/phpunit/phpunit.xsd"
        bootstrap="tests/bootstrap.php"
        colors="true"
        backupGlobals="true"
        backupStaticAttributes="false"
        convertErrorsToExceptions="true"
        convertNoticesToExceptions="true"
        convertWarningsToExceptions="true"
        processIsolation="false"
        stopOnFailure="false">
    <testsuites>
        <testsuite name="Browser">
            <directory>tests/Browser</directory>
        </testsuite>
    </testsuites>
    <php>
        <env name="APP_ENV" value="testing"/>
        <env name="DUSK_DRIVER_URL" value="http://localhost:{geckodriver_port}"/>
        <env name="APP_URL" value="http://localhost:{server_port}"/>
    </php>
</phpunit>
"""
    
    with open("phpunit.dusk.xml", "w") as f:
        f.write(config_content)
    
    print_colored("Custom phpunit.dusk.xml configuration created.", Colors.GREEN)


def clear_screenshots():
    """Clear previous screenshots."""
    print_header("Clearing previous screenshots")
    
    screenshot_dir = os.path.join("tests", "Browser", "screenshots")
    if os.path.exists(screenshot_dir):
        for file in glob.glob(os.path.join(screenshot_dir, "*.png")):
            try:
                os.remove(file)
            except:
                pass
    else:
        os.makedirs(screenshot_dir, exist_ok=True)
    
    print_colored("Previous screenshots cleared.", Colors.GREEN)


def start_laravel_server(port):
    """Start Laravel development server."""
    print_header(f"Starting Laravel development server on port {port}")
    
    if platform.system() == 'Windows':
        cmd = f"start /B cmd /c \"php artisan serve --port={port}\" > server.log 2>&1"
        subprocess.Popen(cmd, shell=True)
    else:
        cmd = f"php artisan serve --port={port} > server.log 2>&1 &"
        subprocess.Popen(cmd, shell=True)
    
    print_colored("Waiting for server to start...", Colors.YELLOW)
    time.sleep(5)
    
    print_colored(f"Laravel development server started on port {port}", Colors.GREEN)


def run_dusk_tests():
    """Run Laravel Dusk tests."""
    print_header("Running Laravel Dusk tests")
    
    # Set environment variables
    env = os.environ.copy()
    
    # Run Dusk tests
    result = run_command("php artisan dusk --configuration=phpunit.dusk.xml", env=env, check=False)
    
    if result.returncode == 0:
        print_colored("All tests passed!", Colors.GREEN)
    else:
        print_colored("Some tests failed. Check the output above for details.", Colors.RED)
        print_colored("Screenshots of failed tests are available in tests/Browser/screenshots/", Colors.RED)
    
    return result.returncode


def generate_test_report():
    """Generate a simple HTML test report."""
    print_header("Generating test report")
    
    # Create report directory if it doesn't exist
    report_dir = os.path.join("tests", "Browser", "reports")
    os.makedirs(report_dir, exist_ok=True)
    
    # Get current date and time
    now = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
    
    # Create HTML report
    report_content = f"""<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dusk Test Report</title>
    <style>
        body {{
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            color: #333;
        }}
        h1, h2 {{
            color: #2c3e50;
        }}
        .container {{
            max-width: 1200px;
            margin: 0 auto;
        }}
        .screenshot {{
            margin-bottom: 20px;
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 4px;
        }}
        .screenshot img {{
            max-width: 100%;
            height: auto;
            display: block;
            margin-top: 10px;
        }}
        .timestamp {{
            color: #7f8c8d;
            font-size: 0.9em;
        }}
    </style>
</head>
<body>
    <div class="container">
        <h1>Laravel Dusk Test Report</h1>
        <p class="timestamp">Generated on: {now}</p>
        
        <h2>Test Screenshots</h2>
        <div class="screenshots">
"""
    
    # Add screenshots to the report
    screenshot_dir = os.path.join("tests", "Browser", "screenshots")
    if os.path.exists(screenshot_dir):
        for file in glob.glob(os.path.join(screenshot_dir, "*.png")):
            filename = os.path.basename(file)
            report_content += f"""            <div class="screenshot">
                <h3>{filename}</h3>
                <img src="../screenshots/{filename}" alt="{filename}">
            </div>
"""
    
    # Close the HTML file
    report_content += """        </div>
    </div>
</body>
</html>
"""
    
    with open(os.path.join(report_dir, "report.html"), "w") as f:
        f.write(report_content)
    
    print_colored(f"Test report generated at {os.path.join(report_dir, 'report.html')}", Colors.GREEN)


def cleanup(server_port):
    """Clean up processes after tests."""
    print_header("Cleaning up processes")
    
    # Stop the Laravel development server
    if platform.system() == 'Windows':
        run_command(f"for /f \"tokens=5\" %a in ('netstat -ano ^| findstr :{server_port}') do taskkill /F /PID %a >nul 2>&1", check=False)
    else:
        # Find process listening on server_port and kill it
        result = run_command(f"lsof -i :{server_port} -t", check=False)
        if result.returncode == 0:
            for pid in result.stdout.strip().split('\n'):
                if pid:
                    run_command(f"kill -9 {pid}", check=False)
    
    # Kill GeckoDriver and Firefox
    kill_processes()
    
    print_colored("Cleanup completed.", Colors.GREEN)


def main():
    """Main function to run Dusk tests."""
    print_header("Starting Laravel Dusk End-to-End Tests with Firefox")
    
    # Setup virtual environment
    python_executable = setup_virtual_environment()
    
    # Kill any existing Firefox and GeckoDriver processes
    kill_processes()
    
    # Check/Install GeckoDriver
    if not check_geckodriver():
        return 1
    
    # Define GeckoDriver port
    geckodriver_port = 4444
    
    # Start GeckoDriver
    if not start_geckodriver(geckodriver_port):
        return 1
    
    # Clear previous screenshots
    clear_screenshots()
    
    # Find an available port for Laravel server
    server_port = find_available_port()
    if not server_port:
        return 1
    
    # Create custom phpunit configuration
    create_phpunit_config(server_port, geckodriver_port)
    
    # Start Laravel development server
    start_laravel_server(server_port)
    
    # Run tests
    test_result = run_dusk_tests()
    
    # Generate test report
    generate_test_report()
    
    # Clean up
    cleanup(server_port)
    
    print_header("Test execution completed")
    return test_result


if __name__ == "__main__":
    try:
        sys.exit(main())
    except KeyboardInterrupt:
        print_colored("\nScript interrupted by user. Cleaning up...", Colors.YELLOW)
        # Try to kill any remaining processes
        kill_processes()
        sys.exit(1)
    except Exception as e:
        print_colored(f"\nAn error occurred: {str(e)}", Colors.RED)
        # Try to kill any remaining processes
        kill_processes()
        sys.exit(1)
