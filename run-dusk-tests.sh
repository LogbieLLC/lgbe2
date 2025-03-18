#!/bin/bash

# Script to run Laravel Dusk tests and generate a report

# Set colors for output
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[0;33m'
NC='\033[0m' # No Color

echo -e "${YELLOW}Starting Laravel Dusk End-to-End Tests${NC}"
echo "========================================"

# Ensure Chrome driver is running
echo -e "${YELLOW}Starting Chrome driver...${NC}"

# Kill any existing Chrome processes
echo -e "${YELLOW}Cleaning up existing Chrome processes...${NC}"
if [ "$(uname)" == "Darwin" ]; then
    # macOS
    pkill -f "Google Chrome" || true
    pkill -f "chromedriver" || true
elif [ "$(uname)" == "Linux" ]; then
    # Linux
    pkill -f chrome || true
    pkill -f chromedriver || true
else
    # Windows (Git Bash)
    taskkill //F //IM chrome.exe > /dev/null 2>&1 || true
    taskkill //F //IM chromedriver.exe > /dev/null 2>&1 || true
fi

# Wait for processes to fully terminate
sleep 2

# Install ChromeDriver that matches Chrome version
echo -e "${YELLOW}Installing matching ChromeDriver version...${NC}"
php artisan dusk:chrome-driver --detect

# Define ChromeDriver port
CHROMEDRIVER_PORT=9515
echo -e "${YELLOW}Using ChromeDriver port: ${CHROMEDRIVER_PORT}${NC}"

# Start ChromeDriver in the background with explicit port
echo -e "${YELLOW}Starting ChromeDriver...${NC}"
if [ "$(uname)" == "Darwin" ]; then
    # macOS
    ./vendor/laravel/dusk/bin/chromedriver-mac --port=${CHROMEDRIVER_PORT} > /dev/null 2>&1 &
elif [ "$(uname)" == "Linux" ]; then
    # Linux
    ./vendor/laravel/dusk/bin/chromedriver-linux --port=${CHROMEDRIVER_PORT} > /dev/null 2>&1 &
else
    # Windows
    start //B vendor\\laravel\\dusk\\bin\\chromedriver-win.exe --port=${CHROMEDRIVER_PORT}
fi

CHROMEDRIVER_PID=$!
echo -e "${YELLOW}Started ChromeDriver with PID: ${CHROMEDRIVER_PID}${NC}"

# Wait for ChromeDriver to start
sleep 3

# Verify ChromeDriver is running
echo -e "${YELLOW}Verifying ChromeDriver is running...${NC}"
if [ "$(uname)" == "Darwin" ] || [ "$(uname)" == "Linux" ]; then
    if curl -s http://localhost:${CHROMEDRIVER_PORT}/status > /dev/null; then
        echo -e "${GREEN}ChromeDriver is running successfully on port ${CHROMEDRIVER_PORT}${NC}"
    else
        echo -e "${RED}ChromeDriver is not running. Attempting to restart...${NC}"
        if [ "$(uname)" == "Darwin" ]; then
            ./vendor/laravel/dusk/bin/chromedriver-mac --port=${CHROMEDRIVER_PORT} > /dev/null 2>&1 &
        else
            ./vendor/laravel/dusk/bin/chromedriver-linux --port=${CHROMEDRIVER_PORT} > /dev/null 2>&1 &
        fi
        CHROMEDRIVER_PID=$!
        echo -e "${YELLOW}Restarted ChromeDriver with PID: ${CHROMEDRIVER_PID}${NC}"
        sleep 3
    fi
else
    # Windows - just wait a bit longer
    sleep 2
fi

# Export the ChromeDriver URL for Dusk to use
export DUSK_DRIVER_URL="http://localhost:${CHROMEDRIVER_PORT}"

# Clear previous screenshots
echo -e "${YELLOW}Clearing previous screenshots...${NC}"
rm -rf tests/Browser/screenshots/*

# Run the tests
echo -e "${YELLOW}Running tests...${NC}"
php artisan dusk

# Cleanup ChromeDriver after tests
echo -e "${YELLOW}Cleaning up ChromeDriver process...${NC}"
if [ "$(uname)" == "Darwin" ] || [ "$(uname)" == "Linux" ]; then
    kill $CHROMEDRIVER_PID 2>/dev/null || true
    pkill -f chromedriver || true
    pkill -f chrome || true
else
    taskkill //F //IM chromedriver.exe > /dev/null 2>&1 || true
    taskkill //F //IM chrome.exe > /dev/null 2>&1 || true
fi

# Check if tests passed
if [ $? -eq 0 ]; then
    echo -e "${GREEN}All tests passed!${NC}"
else
    echo -e "${RED}Some tests failed. Check the output above for details.${NC}"
    echo -e "${YELLOW}Screenshots of failed tests are available in tests/Browser/screenshots/${NC}"
fi

# Generate a simple HTML report
echo -e "${YELLOW}Generating test report...${NC}"

# Create report directory if it doesn't exist
mkdir -p tests/Browser/reports

# Get current date and time
DATE=$(date +"%Y-%m-%d %H:%M:%S")

# Create HTML report
cat > tests/Browser/reports/report.html << EOF
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dusk Test Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        h1, h2 {
            color: #2c3e50;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .screenshot {
            margin-bottom: 20px;
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 4px;
        }
        .screenshot img {
            max-width: 100%;
            height: auto;
            display: block;
            margin-top: 10px;
        }
        .timestamp {
            color: #7f8c8d;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Laravel Dusk Test Report</h1>
        <p class="timestamp">Generated on: $DATE</p>
        
        <h2>Test Screenshots</h2>
        <div class="screenshots">
EOF

# Add screenshots to the report
for screenshot in tests/Browser/screenshots/*.png; do
    if [ -f "$screenshot" ]; then
        filename=$(basename "$screenshot")
        echo "            <div class=\"screenshot\">" >> tests/Browser/reports/report.html
        echo "                <h3>$filename</h3>" >> tests/Browser/reports/report.html
        echo "                <img src=\"../screenshots/$filename\" alt=\"$filename\">" >> tests/Browser/reports/report.html
        echo "            </div>" >> tests/Browser/reports/report.html
    fi
done

# Close the HTML file
cat >> tests/Browser/reports/report.html << EOF
        </div>
    </div>
</body>
</html>
EOF

echo -e "${GREEN}Test report generated at tests/Browser/reports/report.html${NC}"
echo "========================================"
