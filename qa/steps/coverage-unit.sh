#!/bin/bash

MIN_COVERAGE=50

echo "📈 Running Unit-Test coverage only..."

output=$(docker compose run --rm \
    -e XDEBUG_MODE=coverage \
    app ./vendor/bin/phpunit \
    --testsuite Unit \
    --coverage-text \
    --coverage-html=coverage-report/unit)

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
