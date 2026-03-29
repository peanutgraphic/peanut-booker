#!/bin/bash

##############################################################################
# Peanut Booker - Accessibility Check Script
#
# Runs comprehensive accessibility checks on the frontend using jest-axe
# and eslint-plugin-jsx-a11y
#
# Usage:
#   ./scripts/a11y-check.sh                 # Run all a11y checks
#   ./scripts/a11y-check.sh --unit          # Run unit a11y tests only
#   ./scripts/a11y-check.sh --eslint        # Run ESLint a11y rules only
#   ./scripts/a11y-check.sh --coverage      # Run with coverage report
#
##############################################################################

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Script directory
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
PROJECT_ROOT="$( cd "$SCRIPT_DIR/.." && pwd )"
FRONTEND_DIR="$PROJECT_ROOT/frontend"

echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}Peanut Booker - Accessibility Check${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""

# Parse arguments
RUN_UNIT=true
RUN_ESLINT=true
RUN_COVERAGE=false

while [[ $# -gt 0 ]]; do
  case $1 in
    --unit)
      RUN_ESLINT=false
      shift
      ;;
    --eslint)
      RUN_UNIT=false
      shift
      ;;
    --coverage)
      RUN_COVERAGE=true
      shift
      ;;
    --help)
      echo "Usage: ./scripts/a11y-check.sh [options]"
      echo ""
      echo "Options:"
      echo "  --unit       Run unit a11y tests only"
      echo "  --eslint     Run ESLint a11y rules only"
      echo "  --coverage   Run with coverage report"
      echo "  --help       Show this help message"
      exit 0
      ;;
    *)
      echo "Unknown option: $1"
      exit 1
      ;;
  esac
done

cd "$FRONTEND_DIR"

# Run ESLint with a11y rules
if [ "$RUN_ESLINT" = true ]; then
  echo -e "${YELLOW}Running ESLint accessibility checks...${NC}"
  echo ""

  if npx eslint --ext .js,.jsx,.ts,.tsx src/ \
    --rule 'jsx-a11y/alt-text: warn' \
    --rule 'jsx-a11y/anchor-has-content: warn' \
    --rule 'jsx-a11y/aria-props: error' \
    --rule 'jsx-a11y/aria-role: error' \
    --rule 'jsx-a11y/aria-unsupported-elements: error' \
    --rule 'jsx-a11y/click-events-have-key-events: warn' \
    --rule 'jsx-a11y/heading-has-content: warn' \
    --rule 'jsx-a11y/html-has-lang: warn' \
    --rule 'jsx-a11y/iframe-has-title: warn' \
    --rule 'jsx-a11y/img-redundant-alt: warn' \
    --rule 'jsx-a11y/label-has-associated-control: warn' \
    --rule 'jsx-a11y/media-has-caption: warn' \
    --rule 'jsx-a11y/no-access-key: warn' \
    --rule 'jsx-a11y/no-autofocus: warn' \
    --rule 'jsx-a11y/no-distracting-elements: warn' \
    --rule 'jsx-a11y/no-interactive-element-to-noninteractive-role: warn' \
    --rule 'jsx-a11y/no-noninteractive-element-interactions: warn' \
    --rule 'jsx-a11y/no-noninteractive-element-to-interactive-role: warn' \
    --rule 'jsx-a11y/no-redundant-roles: warn' \
    --rule 'jsx-a11y/no-static-element-interactions: warn' \
    --rule 'jsx-a11y/role-has-required-aria-props: error' \
    --rule 'jsx-a11y/role-supports-aria-props: error' \
    --rule 'jsx-a11y/scope: warn'; then
    echo -e "${GREEN}✓ ESLint accessibility checks passed${NC}"
  else
    echo -e "${RED}✗ ESLint accessibility checks failed${NC}"
    EXIT_CODE=1
  fi
  echo ""
fi

# Run vitest a11y tests
if [ "$RUN_UNIT" = true ]; then
  echo -e "${YELLOW}Running vitest accessibility tests...${NC}"
  echo ""

  TEST_ARGS="run --reporter=verbose"

  if [ "$RUN_COVERAGE" = true ]; then
    TEST_ARGS="$TEST_ARGS --coverage"
  fi

  if npm run test:a11y -- $TEST_ARGS 2>&1 | tee /tmp/vitest-output.log; then
    echo ""
    echo -e "${GREEN}✓ Vitest accessibility tests passed${NC}"
  else
    echo ""
    echo -e "${RED}✗ Vitest accessibility tests failed${NC}"
    EXIT_CODE=1
  fi
  echo ""
fi

# Summary
echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}Accessibility Check Summary${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""

if [ -z "$EXIT_CODE" ]; then
  echo -e "${GREEN}✓ All accessibility checks passed!${NC}"
  echo ""
  echo "Guidelines for maintaining accessibility:"
  echo "  • Use semantic HTML (heading, button, form, etc.)"
  echo "  • Provide alt text for images"
  echo "  • Ensure proper label associations for form inputs"
  echo "  • Use ARIA attributes appropriately"
  echo "  • Test with keyboard navigation"
  echo "  • Maintain sufficient color contrast (WCAG AA: 4.5:1)"
  echo "  • Avoid color-only status indicators"
  echo ""
  exit 0
else
  echo -e "${RED}✗ Some accessibility checks failed${NC}"
  echo ""
  echo "Please fix the issues above before proceeding."
  echo ""
  exit 1
fi
