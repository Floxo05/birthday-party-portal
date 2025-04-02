#!/bin/bash
export APP_ENV=test

echo "🔁 Bereite Testdatenbank vor..."
php bin/console doctrine:database:drop --force --if-exists
php bin/console doctrine:database:create
php bin/console doctrine:schema:update --force

export XDEBUG_MODE=off

echo "🧪 Führe Feature-Tests aus..."
./vendor/bin/phpunit --testsuite=feature