#!/bin/bash

# ANSI color codes
GREEN='\033[0;32m'
YELLOW='\033[0;33m'
RED='\033[0;31m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "\n========================================="
echo -e "Running tests in order of operations"
echo -e "=========================================\n"

# Define test steps
STEPS=("PHP_CodeSniffer" "PHPStan" "ESLint" "Pest" "Jest")
COMMANDS=("vendor/bin/phpcs" "vendor/bin/phpstan analyse --memory-limit=512M" "npm run lint" "vendor/bin/pest" "npm test")
DESCRIPTIONS=(
    "Enforces coding standards for PHP, ensuring the code is consistent, readable, and adheres to best practices."
    "Performs static analysis on PHP code to uncover potential bugs, type errors, and logical inconsistencies."
    "Lints JavaScript code within Vue.js components to enforce coding standards and flag common errors."
    "Runs unit and integration tests for PHP code to verify that individual components and their interactions work correctly."
    "Executes unit tests for JavaScript code in Vue.js components, ensuring they function as expected in isolation."
)

# Initialize variables
FAILED_STEPS=()
TOTAL_STEPS=${#STEPS[@]}
ALL_PASSED=true

# Parse command line arguments
RUN_ALL=false
SPECIFIC_STEP=0
CONTINUE_ON_ERROR=false

function usage {
    echo -e "\nUsage: ./run-tests.sh [options]"
    echo -e "\nOptions:"
    echo "  --all         Run all tests"
    echo "  --continue    Continue running tests even if a step fails"
    echo "  --step N      Run only step N (1-5)"
    echo "  --help        Display this help message"
    echo -e "\nTest Steps:"
    echo "  1. PHP_CodeSniffer - PHP coding standards"
    echo "  2. PHPStan - PHP static analysis"
    echo "  3. ESLint - JavaScript/Vue linting"
    echo "  4. Pest - PHP unit/integration tests"
    echo "  5. Jest - JavaScript unit tests"
    echo ""
    exit 1
}

# Parse arguments
while [[ "$#" -gt 0 ]]; do
    case $1 in
        --all) RUN_ALL=true ;;
        --continue) CONTINUE_ON_ERROR=true ;;
        --step) 
            if [[ -z "$2" || ! "$2" =~ ^[1-5]$ ]]; then
                echo -e "${RED}Error: --step requires a number parameter between 1 and 5.${NC}"
                usage
            fi
            SPECIFIC_STEP=$2
            shift
            ;;
        --help) usage ;;
        *) echo -e "${RED}Unknown parameter: $1${NC}"; usage ;;
    esac
    shift
done

# No longer need to make Dusk script executable

# Function to run a test step
run_step() {
    local step=$1
    local index=$((step-1))
    
    echo -e "\n${BLUE}Step $step/$TOTAL_STEPS: ${STEPS[$index]}${NC}"
    echo -e "${BLUE}Description: ${DESCRIPTIONS[$index]}${NC}\n"
    
    eval "${COMMANDS[$index]}"
    
    if [ $? -ne 0 ]; then
        echo -e "${RED}Step $step (${STEPS[$index]}) failed with error code $?.${NC}"
        return 1
    else
        echo -e "${GREEN}Step $step (${STEPS[$index]}) completed successfully.${NC}"
        return 0
    fi
}

# Run tests
if [ $SPECIFIC_STEP -ne 0 ]; then
    run_step $SPECIFIC_STEP
    if [ $? -ne 0 ]; then
        ALL_PASSED=false
        FAILED_STEPS+=($SPECIFIC_STEP)
    fi
else
    for ((i=1; i<=$TOTAL_STEPS; i++)); do
        run_step $i
        if [ $? -ne 0 ]; then
            ALL_PASSED=false
            FAILED_STEPS+=($i)
            
            if ! $CONTINUE_ON_ERROR && [ $i -lt $TOTAL_STEPS ]; then
                echo -e "\n${RED}Test step $i failed. Stopping test execution.${NC}"
                echo -e "${YELLOW}Use --continue flag to continue testing despite failures.${NC}"
                break
            fi
        fi
    done
fi

# Print summary
echo -e "\n========================================="
echo -e "Test Execution Summary"
echo -e "=========================================\n"

if $ALL_PASSED; then
    if [ $SPECIFIC_STEP -ne 0 ]; then
        echo -e "${GREEN}Step $SPECIFIC_STEP (${STEPS[$SPECIFIC_STEP-1]}) passed successfully.${NC}"
    else
        echo -e "${GREEN}All test steps passed successfully!${NC}"
    fi
else
    echo -e "${RED}The following test steps failed:${NC}"
    for step in "${FAILED_STEPS[@]}"; do
        echo -e "${RED} - Step $step: ${STEPS[$step-1]}${NC}"
    done
fi

echo -e "\n========================================="
