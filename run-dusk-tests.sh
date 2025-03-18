#!/bin/bash

# Script to run Laravel Dusk tests with Firefox and GeckoDriver
# Updated to match Windows batch file functionality

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

# Create a wrapper script for Firefox if needed
FIREFOX_WRAPPER="/tmp/firefox-wrapper.sh"
echo -e "${YELLOW}Creating Firefox wrapper script at ${FIREFOX_WRAPPER}...${NC}"
cat > ${FIREFOX_WRAPPER} << 'EOF'
#!/bin/bash
# Firefox wrapper script for Dusk tests
exec /usr/bin/firefox "$@"
EOF
chmod +x ${FIREFOX_WRAPPER}
echo -e "${GREEN}Firefox wrapper script created successfully.${NC}"

# Verify GeckoDriver is installed
echo -e "${YELLOW}Verifying GeckoDriver installation...${NC}"
if ! command -v geckodriver &> /dev/null; then
    echo -e "${YELLOW}GeckoDriver not found. Installing GeckoDriver...${NC}"
    
    # Create temp directory
    mkdir -p temp
    cd temp
    
    # Set GeckoDriver version
    GECKODRIVER_VERSION="v0.33.0"
    
    # Determine system architecture and OS
    if [ "$(uname)" == "Darwin" ]; then
        # macOS
        if [ "$(uname -m)" == "arm64" ]; then
            # Apple Silicon (M1/M2)
            GECKODRIVER_ARCHIVE="geckodriver-${GECKODRIVER_VERSION}-macos-aarch64.tar.gz"
        else
            # Intel Mac
            GECKODRIVER_ARCHIVE="geckodriver-${GECKODRIVER_VERSION}-macos.tar.gz"
        fi
    else
        # Linux
        if [ "$(uname -m)" == "aarch64" ]; then
            # ARM64 Linux
            GECKODRIVER_ARCHIVE="geckodriver-${GECKODRIVER_VERSION}-linux-aarch64.tar.gz"
        else
            # x86_64 Linux
            GECKODRIVER_ARCHIVE="geckodriver-${GECKODRIVER_VERSION}-linux64.tar.gz"
        fi
    fi
    
    # Download GeckoDriver
    echo -e "${YELLOW}Downloading ${GECKODRIVER_ARCHIVE}...${NC}"
    if command -v wget &> /dev/null; then
        if ! wget -q "https://github.com/mozilla/geckodriver/releases/download/${GECKODRIVER_VERSION}/${GECKODRIVER_ARCHIVE}"; then
            echo -e "${RED}Failed to download GeckoDriver with wget. Please check your internet connection.${NC}"
            cd ..
            rm -rf temp
            exit 1
        fi
    elif command -v curl &> /dev/null; then
        if ! curl -sL -o "${GECKODRIVER_ARCHIVE}" "https://github.com/mozilla/geckodriver/releases/download/${GECKODRIVER_VERSION}/${GECKODRIVER_ARCHIVE}"; then
            echo -e "${RED}Failed to download GeckoDriver with curl. Please check your internet connection.${NC}"
            cd ..
            rm -rf temp
            exit 1
        fi
    else
        echo -e "${RED}Neither wget nor curl is installed. Please install one of them.${NC}"
        cd ..
        rm -rf temp
        exit 1
    fi
    
    # Check if download was successful
    if [ ! -f "${GECKODRIVER_ARCHIVE}" ]; then
        echo -e "${RED}Download failed. GeckoDriver archive not found.${NC}"
        echo -e "${YELLOW}Please download manually from:${NC}"
        echo -e "https://github.com/mozilla/geckodriver/releases/download/${GECKODRIVER_VERSION}/${GECKODRIVER_ARCHIVE}"
        echo -e "${YELLOW}Extract the archive and place geckodriver in your PATH.${NC}"
        cd ..
        rm -rf temp
        exit 1
    fi
    
    # Extract GeckoDriver with error handling
    echo -e "${YELLOW}Extracting GeckoDriver...${NC}"
    if ! tar -xzf "${GECKODRIVER_ARCHIVE}"; then
        echo -e "${RED}Failed to extract GeckoDriver archive.${NC}"
        echo -e "${YELLOW}Trying alternative extraction method...${NC}"
        
        # Try unzip as a fallback if available
        if command -v unzip &> /dev/null; then
            if [[ "${GECKODRIVER_ARCHIVE}" == *.zip ]]; then
                unzip -o "${GECKODRIVER_ARCHIVE}"
                if [ $? -ne 0 ]; then
                    echo -e "${RED}Failed to extract with unzip.${NC}"
                    cd ..
                    rm -rf temp
                    exit 1
                else
                    echo -e "${GREEN}Extraction with unzip successful.${NC}"
                fi
            else
                echo -e "${RED}Archive is not a zip file, cannot use unzip.${NC}"
                cd ..
                rm -rf temp
                exit 1
            fi
        else
            echo -e "${RED}No alternative extraction methods available.${NC}"
            cd ..
            rm -rf temp
            exit 1
        fi
    fi
    
    # Check if geckodriver binary was extracted
    if [ ! -f "geckodriver" ]; then
        echo -e "${RED}Extraction failed. GeckoDriver binary not found.${NC}"
        cd ..
        rm -rf temp
        exit 1
    fi
    
    # Move GeckoDriver to project directory
    echo -e "${YELLOW}Installing GeckoDriver...${NC}"
    cd ..
    chmod +x temp/geckodriver
    
    # Try to install globally if we have sudo access, otherwise install locally
    if command -v sudo &> /dev/null && sudo -n true 2>/dev/null; then
        echo -e "${YELLOW}Installing GeckoDriver globally...${NC}"
        sudo mv temp/geckodriver /usr/local/bin/
    else
        echo -e "${YELLOW}Installing GeckoDriver locally...${NC}"
        mv temp/geckodriver ./
        # Add current directory to PATH for this session
        export PATH=$PATH:$(pwd)
    fi
    
    # Clean up
    echo -e "${YELLOW}Cleaning up...${NC}"
    rm -rf temp
    
    echo -e "${GREEN}GeckoDriver installed successfully.${NC}"
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

# Find an available port for the Laravel development server
echo -e "${YELLOW}Finding available port for Laravel development server...${NC}"
SERVER_PORT=8000
while netstat -tuln | grep -q ":$SERVER_PORT "; do
    SERVER_PORT=$((SERVER_PORT+1))
    if [ $SERVER_PORT -gt 8020 ]; then
        echo -e "${RED}No available ports found between 8000 and 8020.${NC}"
        echo -e "${RED}Please free up a port or modify the script to use a different port range.${NC}"
        exit 1
    fi
done
echo -e "${YELLOW}Using port ${SERVER_PORT} for Laravel development server${NC}"

# Create custom phpunit.dusk.xml configuration for Firefox
echo -e "${YELLOW}Creating custom phpunit.dusk.xml configuration...${NC}"
cat > phpunit.dusk.xml << EOFXML
<?xml version="1.0" encoding="UTF-8"?>
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
        <env name="DUSK_DRIVER_URL" value="http://localhost:${GECKODRIVER_PORT}"/>
        <env name="APP_URL" value="http://localhost:${SERVER_PORT}"/>
    </php>
</phpunit>
EOFXML

# Start Laravel development server in the background
echo -e "${YELLOW}Starting Laravel development server...${NC}"
php artisan serve --port=${SERVER_PORT} > server.log 2>&1 &
SERVER_PID=$!
echo -e "${YELLOW}Started Laravel development server with PID: ${SERVER_PID}${NC}"
echo -e "${YELLOW}Waiting for server to start...${NC}"
sleep 5

# Update the test environment to use the correct port
export APP_URL="http://localhost:${SERVER_PORT}"

# Run the tests with custom configuration
echo -e "${YELLOW}Running tests...${NC}"
php artisan dusk --configuration=phpunit.dusk.xml
TEST_RESULT=$?

# Stop the Laravel development server
echo -e "${YELLOW}Stopping Laravel development server...${NC}"
kill $SERVER_PID 2>/dev/null || true

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
if [ $TEST_RESULT -eq 0 ]; then
    echo -e "${GREEN}All tests passed!${NC}"
else
    echo -e "${RED}Some tests failed. Check the output above for details.${NC}"
    echo -e "${RED}Screenshots of failed tests are available in tests/Browser/screenshots/${NC}"
fi

# Generate a simple HTML report
echo -e "${YELLOW}Generating test report...${NC}"

# Create report directory if it doesn't exist
mkdir -p tests/Browser/reports/

# Get current date and time
DATE=$(date "+%Y-%m-%d %H:%M:%S")

# Create HTML report
cat > tests/Browser/reports/report.html << EOFHTML
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
        <p class="timestamp">Generated on: ${DATE}</p>
        
        <h2>Test Screenshots</h2>
        <div class="screenshots">
EOFHTML

# Add screenshots to the report
for screenshot in tests/Browser/screenshots/*.png; do
    if [ -f "$screenshot" ]; then
        filename=$(basename "$screenshot")
        echo "            <div class=\"screenshot\">" >> tests/Browser/reports/report.html
        echo "                <h3>${filename}</h3>" >> tests/Browser/reports/report.html
        echo "                <img src=\"../screenshots/${filename}\" alt=\"${filename}\">" >> tests/Browser/reports/report.html
        echo "            </div>" >> tests/Browser/reports/report.html
    fi
done

# Close the HTML file
cat >> tests/Browser/reports/report.html << EOFHTML
        </div>
    </div>
</body>
</html>
EOFHTML

echo -e "${YELLOW}Test report generated at tests/Browser/reports/report.html${NC}"
echo "========================================"

exit $TEST_RESULT
