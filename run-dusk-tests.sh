#!/bin/bash

# Script to run Laravel Dusk tests with Firefox and GeckoDriver
# Updated to use Firefox instead of Chrome

# Set colors for output
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[0;33m'
NC='\033[0m' # No Color

echo -e "${YELLOW}Starting Laravel Dusk End-to-End Tests with Firefox${NC}"
echo "========================================"

# Ensure GeckoDriver is running
echo -e "${YELLOW}Setting up Firefox and GeckoDriver...${NC}"

# Kill any existing Firefox processes
echo -e "${YELLOW}Cleaning up existing Firefox processes...${NC}"
if [ "$(uname)" == "Darwin" ]; then
    # macOS
    pkill -f firefox || true
    pkill -f geckodriver || true
elif [ "$(uname)" == "Linux" ]; then
    # Linux
    pkill -f firefox || true
    pkill -f geckodriver || true
else
    # Windows (Git Bash)
    taskkill //F //IM firefox.exe > /dev/null 2>&1 || true
    taskkill //F //IM geckodriver.exe > /dev/null 2>&1 || true
fi

# Wait for processes to fully terminate
sleep 2

# Define GeckoDriver port
GECKODRIVER_PORT=4444
echo -e "${YELLOW}Using GeckoDriver port: ${GECKODRIVER_PORT}${NC}"

# Install netstat if not available
if ! command -v netstat &> /dev/null; then
    echo -e "${YELLOW}Installing net-tools for netstat...${NC}"
    sudo apt-get update -qq && sudo apt-get install -y net-tools
fi

# Check if port is already in use
if command -v netstat &> /dev/null; then
    PORT_IN_USE=$(netstat -tuln | grep ":${GECKODRIVER_PORT} " | wc -l)
    if [ "$PORT_IN_USE" -gt 0 ]; then
        echo -e "${RED}Port ${GECKODRIVER_PORT} is already in use. Trying to free it...${NC}"
        sudo fuser -k ${GECKODRIVER_PORT}/tcp || true
        sleep 2
    fi
fi

# Verify Firefox is installed
echo -e "${YELLOW}Verifying Firefox installation...${NC}"
if ! command -v firefox &> /dev/null; then
    echo -e "${RED}Firefox not found. Installing Firefox...${NC}"
    sudo apt-get update && sudo apt-get install -y firefox
fi

# Verify GeckoDriver is installed
echo -e "${YELLOW}Verifying GeckoDriver installation...${NC}"
if ! command -v geckodriver &> /dev/null; then
    echo -e "${RED}GeckoDriver not found. Installing GeckoDriver...${NC}"
    GECKODRIVER_VERSION="v0.33.0"
    wget https://github.com/mozilla/geckodriver/releases/download/${GECKODRIVER_VERSION}/geckodriver-${GECKODRIVER_VERSION}-linux64.tar.gz
    tar -xvzf geckodriver-${GECKODRIVER_VERSION}-linux64.tar.gz
    sudo mv geckodriver /usr/local/bin/
    sudo chmod +x /usr/local/bin/geckodriver
fi

# Start GeckoDriver in the background with explicit port
echo -e "${YELLOW}Starting GeckoDriver...${NC}"
geckodriver --port ${GECKODRIVER_PORT} > /tmp/geckodriver.log 2>&1 &
GECKODRIVER_PID=$!
echo -e "${YELLOW}Started GeckoDriver with PID: ${GECKODRIVER_PID}${NC}"

# Wait for GeckoDriver to start
sleep 3

# Verify GeckoDriver is running
echo -e "${YELLOW}Verifying GeckoDriver is running...${NC}"
if curl -s http://localhost:${GECKODRIVER_PORT}/status > /dev/null; then
    echo -e "${GREEN}GeckoDriver is running successfully on port ${GECKODRIVER_PORT}${NC}"
else
    echo -e "${RED}GeckoDriver is not running. Attempting to restart...${NC}"
    cat /tmp/geckodriver.log
    
    # Try restarting GeckoDriver
    pkill -f geckodriver || true
    sleep 2
    geckodriver --port ${GECKODRIVER_PORT} > /tmp/geckodriver.log 2>&1 &
    GECKODRIVER_PID=$!
    echo -e "${YELLOW}Restarted GeckoDriver with PID: ${GECKODRIVER_PID}${NC}"
    sleep 3
    
    # Check again
    if ! curl -s http://localhost:${GECKODRIVER_PORT}/status > /dev/null; then
        echo -e "${RED}GeckoDriver failed to start. See log:${NC}"
        cat /tmp/geckodriver.log
        exit 1
    fi
fi

# Export the GeckoDriver URL for Dusk to use
export DUSK_DRIVER_URL="http://localhost:${GECKODRIVER_PORT}"

# Clear previous screenshots
echo -e "${YELLOW}Clearing previous screenshots...${NC}"
rm -rf tests/Browser/screenshots/*

# Create custom phpunit.dusk.xml configuration for Firefox
echo -e "${YELLOW}Creating custom phpunit.dusk.xml configuration...${NC}"
cat > phpunit.dusk.xml << EOFXML
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="vendor/phpunit/phpunit/phpunit.xsd"
        bootstrap="tests/bootstrap.php"
        colors="true">
    <testsuites>
        <testsuite name="Browser">
            <directory>tests/Browser</directory>
        </testsuite>
    </testsuites>
    <php>
        <env name="APP_ENV" value="testing"/>
        <env name="DUSK_DRIVER_URL" value="http://localhost:${GECKODRIVER_PORT}"/>
    </php>
</phpunit>
EOFXML

# Run the tests with custom configuration
echo -e "${YELLOW}Running tests...${NC}"
php artisan dusk --configuration=phpunit.dusk.xml

# Cleanup GeckoDriver after tests
echo -e "${YELLOW}Cleaning up GeckoDriver process...${NC}"
if [ "$(uname)" == "Darwin" ] || [ "$(uname)" == "Linux" ]; then
    kill $GECKODRIVER_PID 2>/dev/null || true
    pkill -f geckodriver || true
    pkill -f firefox || true
else
    taskkill //F //IM geckodriver.exe > /dev/null 2>&1 || true
    taskkill //F //IM firefox.exe > /dev/null 2>&1 || true
fi

# Check if tests passed
if [ $? -eq 0 ]; then
    echo -e "${GREEN}All tests passed!${NC}"
else
    echo -e "${RED}Some tests failed. Check the output above for details.${NC}"
fi
