#!/bin/bash

echo "🔌 Running integration tests (no coverage)..."

docker compose run --rm \
    -e XDEBUG_MODE=off \
    app ./vendor/bin/phpunit \
    --testsuite Integration
