#!/bin/bash
set -e

cd /var/www/html

export COMPOSER_ALLOW_SUPERUSER=1
export COMPOSER_DISABLE_XDEBUG_WARN=1

composer install --ignore-platform-reqs --no-interaction --prefer-dist || \
composer update --ignore-platform-reqs --no-interaction --prefer-dist

php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration || true
