#!/bin/bash
set -e

# Optional: App-Setup (Migration, Cache, Assets)
php bin/console doctrine:migrations:migrate --no-interaction || true
php bin/console cache:clear || true
php bin/console cache:warmup || true
php bin/console asset-map:compile || true

# Start Apache im Vordergrund
exec apache2-foreground
