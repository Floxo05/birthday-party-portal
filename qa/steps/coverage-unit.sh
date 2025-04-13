#!/bin/bash

MIN_COVERAGE=1

echo "📈 Running Unit-Test coverage only..."

export XDEBUG_MODE=coverage


output=$(./vendor/bin/phpunit \
    --testsuite Unit \
    --coverage-text \
    --coverage-html=qa/reports/code-coverage)

echo "$output"

coverage=$(echo "$output" | grep -Eo "Lines:\s+[0-9.]+%" | grep -Eo "[0-9.]+" | head -1)

echo ""
echo "📊 Extracted unit test line coverage: $coverage%"

if (( $(echo "$coverage < $MIN_COVERAGE" | bc -l) )); then
    echo "❌ Unit test coverage below threshold of $MIN_COVERAGE%"
    exit 1
else
    echo "✅ Unit test coverage meets threshold of $MIN_COVERAGE%"
    exit 0
fi
