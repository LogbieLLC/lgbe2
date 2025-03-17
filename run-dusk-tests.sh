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
php artisan dusk:chrome-driver --detect
php artisan dusk:chrome-driver

# Clear previous screenshots
echo -e "${YELLOW}Clearing previous screenshots...${NC}"
rm -rf tests/Browser/screenshots/*

# Run the tests
echo -e "${YELLOW}Running tests...${NC}"
php artisan dusk

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
