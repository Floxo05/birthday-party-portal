#!/bin/bash

echo "🔌 Running integration tests (no coverage)..."

export XDEBUG_MODE=off

./vendor/bin/phpunit --testsuite Integration
