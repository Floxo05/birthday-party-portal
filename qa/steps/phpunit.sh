#!/bin/bash

MIN_COVERAGE=80

echo "📈 Running PHPUnit with code coverage..."
output=$(docker compose run --rm \
    -e XDEBUG_MODE=coverage \
    app ./vendor/bin/phpunit --coverage-text)

echo "$output"

# Extrahiere Coverage-Wert aus "Lines: XX.XX%"
coverage=$(echo "$output" | grep -Eo "Lines:\s+[0-9.]+%" | grep -Eo "[0-9.]+" | head -1)

echo ""
echo "📊 Extracted line coverage: $coverage%"

# Mit bc vergleichen
if (( $(echo "$coverage < $MIN_COVERAGE" | bc -l) )); then
    echo "❌ Coverage below threshold of $MIN_COVERAGE%"
    exit 1
else
    echo "✅ Coverage meets threshold of $MIN_COVERAGE%"
    exit 0
fi
