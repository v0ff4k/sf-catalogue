#!/bin/bash
set -e

cd /var/www/html

echo "Running migrations..."
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration || true

echo "Loading fixtures..."
php bin/console doctrine:fixtures:load --no-interaction --append || true

exec "$@"
