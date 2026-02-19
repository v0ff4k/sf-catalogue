FROM php:8.3-cli

WORKDIR /var/www/html

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY composer.json symfony.lock ./

RUN composer install --ignore-platform-reqs --no-interaction || true

COPY . .

RUN composer dump-autoload --optimize || true

EXPOSE 8000

CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]
