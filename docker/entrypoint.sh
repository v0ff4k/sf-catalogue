#!/bin/bash
set -e

echo "Waiting for database..."
for i in {1..30}; do
    if php -r "try { new PDO('mysql:host=db;dbname=catalogue', 'root', 'root'); exit(0); } catch (Exception \$e) { exit(1); }" 2>/dev/null; then
        echo "Database is ready!"
        break
    fi
    echo "Attempt $i/30: Database not ready, waiting..."
    sleep 2
done

echo "Running migrations..."
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration || true

exec "$@"
