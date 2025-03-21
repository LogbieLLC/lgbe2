#!/usr/bin/env python3
"""
Startup script for LGBE2 Vite development server.

This script:
1. Creates and uses a virtual environment
2. Installs required dependencies
3. Runs the Vite development server with hot module replacement
4. Provides clean exit handling
"""

import os
import sys
import platform
import subprocess
import signal
import time
from pathlib import Path


# ANSI colors for terminal output
class Colors:
    GREEN = '\033[0;32m'
    RED = '\033[0;31m'
    YELLOW = '\033[0;33m'
    BLUE = '\033[0;34m'
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
    run_command(f"{python_executable} -m pip install psutil", check=True)
    
    return python_executable


def start_vite_server():
    """Start the Vite development server."""
    print_header("Starting Vite development server")
    
    # Set up signal handler for graceful shutdown
    vite_process = None
    
    def signal_handler(sig, frame):
        print_colored("\nShutting down Vite server...", Colors.YELLOW)
        if vite_process:
            if platform.system() == 'Windows':
                # On Windows, terminate the process group
                subprocess.run(f"taskkill /F /T /PID {vite_process.pid}", shell=True)
            else:
                # On Unix, terminate process group
                os.killpg(os.getpgid(vite_process.pid), signal.SIGTERM)
        print_colored("Vite server stopped.", Colors.GREEN)
        sys.exit(0)
    
    # Register signal handlers
    signal.signal(signal.SIGINT, signal_handler)
    signal.signal(signal.SIGTERM, signal_handler)
    
    # Start Vite server
    print_colored("Starting npm run dev...", Colors.YELLOW)
    
    env = os.environ.copy()
    
    if platform.system() == 'Windows':
        # On Windows, just use shell=True without special flags
        vite_process = subprocess.Popen(
            "npm run dev",
            shell=True,
            env=env
        )
    else:
        # Start as a new process group on Unix
        vite_process = subprocess.Popen(
            "npm run dev",
            shell=True,
            env=env,
            preexec_fn=os.setsid
        )
    
    print_colored("Vite development server started.", Colors.GREEN)
    print_colored("Press Ctrl+C to stop the server.", Colors.BLUE)
    
    try:
        # Keep the script running
        while vite_process.poll() is None:
            time.sleep(1)
        
        # If we get here, the process terminated on its own
        exit_code = vite_process.returncode
        if exit_code != 0:
            print_colored(f"Vite server exited with code {exit_code}", Colors.RED)
            return exit_code
        else:
            print_colored("Vite server exited normally", Colors.GREEN)
            return 0
    except KeyboardInterrupt:
        # The signal handler will take care of shutdown
        pass


def main():
    """Main function to run the Vite server."""
    print_header("LGBE2 Vite Development Server")
    
    # Setup virtual environment
    setup_virtual_environment()
    
    # Check if npm is installed
    result = run_command("npm --version", check=False)
    if result.returncode != 0:
        print_colored("npm is not installed or not in PATH.", Colors.RED)
        print_colored("Please install Node.js and npm to continue.", Colors.RED)
        return 1
    
    # Check if node_modules exists
    if not os.path.exists("node_modules"):
        print_colored("node_modules directory not found.", Colors.YELLOW)
        print_colored("Running npm install...", Colors.YELLOW)
        
        result = run_command("npm install", check=False)
        if result.returncode != 0:
            print_colored("npm install failed. Please check error messages above.", Colors.RED)
            return 1
        
        print_colored("npm install completed successfully.", Colors.GREEN)
    
    # Start Vite server
    return start_vite_server()


if __name__ == "__main__":
    try:
        sys.exit(main())
    except Exception as e:
        print_colored(f"\nAn error occurred: {str(e)}", Colors.RED)
        sys.exit(1)
