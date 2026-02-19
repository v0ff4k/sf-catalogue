# SF Catalogue - Headless торговый каталог

Простой и легкий headless сервис торгового каталога на Symfony 6 с Doctrine ORM. REST API для управления товарами без категорий и таксономии.

## Описание

Сервис предоставляет REST API для управления товарами. Каждый товар имеет следующие свойства:

- **название** (name) - строка до 255 символов
- **цена** (price) - десятичное число с точностью до 2 знаков после запятой
- **статус** (status) - активный/неактивный (`active` или `inactive`)
- **дата создания** (createdAt) - автоматически устанавливается при создании

## Технологический стек

- **PHP 8.3**
- **Symfony 6.4**
- **Doctrine ORM 2.16**
- **SQLite** (для разработки)
- **Docker & Docker Compose**

## Требования

- Docker и Docker Compose
- Composer (для локальной разработки)

## Развертывание

### Быстрый старт с Docker

1. Клонируйте репозиторий или перейдите в директорию проекта:
```bash
cd sf-catalogue
```

2. Запустите контейнеры:
```bash
docker-compose up -d --build
```

3. Установите зависимости и загрузите фикстуры:
```bash
docker-compose exec php composer install --no-interaction
docker-compose exec php php bin/console doctrine:migrations:migrate --no-interaction
docker-compose exec php php bin/console doctrine:fixtures:load --no-interaction
```

4. Приложение доступно по адресу: `http://localhost:8080`

### Локальная установка (без Docker)

1. Установите зависимости:
```bash
composer install
```

2. Настройте базу данных в `.env`:
```env
DATABASE_URL="sqlite:///%kernel.project_dir%/var/data.db"
```

3. Выполните миграции:
```bash
php bin/console doctrine:migrations:migrate --no-interaction
```

4. Загрузите тестовые данные:
```bash
php bin/console doctrine:fixtures:load --no-interaction
```

5. Запустите встроенный сервер:
```bash
php -S localhost:8000 -t public
```

## Автоматическая загрузка фикстур

При выполнении `composer install` автоматически запускаются:
- Миграции базы данных
- Загрузка тестовых данных (15 товаров)

Это настроено через скрипты в `composer.json`:
```json
"post-install-cmd": [
    "@auto-scripts",
    "@fixtures-load"
]
```

Для ручного запуска фикстур:
```bash
composer run-script fixtures-load
```

## API Endpoints

Базовый URL: `http://localhost:8080/api/products`

### Создание товара (CREATE)

**POST** `/api/products`

**Заголовки:**
```
Content-Type: application/json
```

**Тело запроса:**
```json
{
    "name": "Название товара",
    "price": "9999.99",
    "status": "active"
}
```

**Пример запроса:**
```bash
curl -X POST http://localhost:8080/api/products \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Ноутбук ASUS",
    "price": "89999.00",
    "status": "active"
  }'
```

**Ответ (201 Created):**
```json
{
    "id": 16,
    "name": "Ноутбук ASUS",
    "price": "89999.00",
    "status": "active",
    "createdAt": "2026-02-19T16:00:00+00:00"
}
```

**Валидация:**
- `name` - обязательное поле, не более 255 символов
- `price` - обязательное поле, числовое значение >= 0
- `status` - может быть только `active` или `inactive` (по умолчанию `active`)

### Получение товара (READ)

**GET** `/api/products/{id}`

**Пример запроса:**
```bash
curl http://localhost:8080/api/products/1
```

**Ответ (200 OK):**
```json
{
    "id": 1,
    "name": "Ноутбук Dell XPS 15",
    "price": "129999.99",
    "status": "active",
    "createdAt": "2026-02-19T15:54:24+00:00"
}
```

**Ошибка (404 Not Found):**
```json
{
    "error": "Product not found"
}
```

### Обновление товара (UPDATE)

**PUT** или **PATCH** `/api/products/{id}`

**Заголовки:**
```
Content-Type: application/json
```

**Тело запроса (все поля опциональны):**
```json
{
    "name": "Обновленное название",
    "price": "79999.00",
    "status": "inactive"
}
```

**Пример запроса:**
```bash
curl -X PUT http://localhost:8080/api/products/1 \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Ноутбук Dell XPS 15 (обновленный)",
    "price": "119999.99",
    "status": "active"
  }'
```

**Ответ (200 OK):**
```json
{
    "id": 1,
    "name": "Ноутбук Dell XPS 15 (обновленный)",
    "price": "119999.99",
    "status": "active",
    "createdAt": "2026-02-19T15:54:24+00:00"
}
```

**Частичное обновление (PATCH):**
```bash
curl -X PATCH http://localhost:8080/api/products/1 \
  -H "Content-Type: application/json" \
  -d '{
    "status": "inactive"
  }'
```

### Удаление товара (DELETE)

**DELETE** `/api/products/{id}`

**Пример запроса:**
```bash
curl -X DELETE http://localhost:8080/api/products/1
```

**Ответ (200 OK):**
```json
{
    "message": "Product deleted"
}
```

**Ошибка (404 Not Found):**
```json
{
    "error": "Product not found"
}
```

## Примеры использования

### Создание нового товара
```bash
curl -X POST http://localhost:8080/api/products \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Смартфон Samsung Galaxy S24",
    "price": "79999.00",
    "status": "active"
  }'
```

### Получение товара по ID
```bash
curl http://localhost:8080/api/products/2
```

### Обновление цены товара
```bash
curl -X PATCH http://localhost:8080/api/products/2 \
  -H "Content-Type: application/json" \
  -d '{"price": "74999.00"}'
```

### Изменение статуса товара
```bash
curl -X PATCH http://localhost:8080/api/products/2 \
  -H "Content-Type: application/json" \
  -d '{"status": "inactive"}'
```

### Удаление товара
```bash
curl -X DELETE http://localhost:8080/api/products/2
```

## Тестовые данные

При установке автоматически загружается 15 тестовых товаров:
- Ноутбук Dell XPS 15
- Смартфон iPhone 15 Pro
- Планшет iPad Air
- Наушники Sony WH-1000XM5
- Клавиатура механическая
- И другие...

## Структура проекта

```
sf-catalogue/
├── config/              # Конфигурационные файлы Symfony
│   ├── packages/        # Конфигурация пакетов
│   └── routes.yaml     # Маршруты
├── docker/             # Docker конфигурация
│   └── nginx/          # Конфигурация Nginx
├── migrations/         # Миграции базы данных
├── public/             # Публичная директория
│   └── index.php       # Точка входа
├── src/
│   ├── Controller/     # REST API контроллеры
│   │   └── ProductController.php
│   ├── DataFixtures/   # Фикстуры для тестовых данных
│   │   └── ProductFixtures.php
│   ├── Entity/         # Doctrine сущности
│   │   └── Product.php
│   └── Kernel.php      # Ядро Symfony
├── tests/              # Тесты
├── var/                # Временные файлы и кэш
├── .env                # Переменные окружения
├── composer.json       # Зависимости Composer
├── docker-compose.yml  # Docker Compose конфигурация
└── Dockerfile          # Docker образ PHP
```

## Команды разработки

### Миграции базы данных
```bash
# Создать новую миграцию
php bin/console make:migration

# Выполнить миграции
php bin/console doctrine:migrations:migrate

# Откатить последнюю миграцию
php bin/console doctrine:migrations:migrate prev
```

### Фикстуры
```bash
# Загрузить фикстуры (очистит базу)
php bin/console doctrine:fixtures:load

# Загрузить фикстуры (без очистки)
php bin/console doctrine:fixtures:load --append
```

### Кэш
```bash
# Очистить кэш
php bin/console cache:clear
```

### Docker команды
```bash
# Запустить контейнеры
docker-compose up -d

# Остановить контейнеры
docker-compose down

# Просмотр логов
docker-compose logs -f php

# Выполнить команду в контейнере
docker-compose exec php bash

# Пересобрать образы
docker-compose build --no-cache
```

## Тестирование

### Запуск тестов
```bash
# Unit тесты
php bin/phpunit tests/Entity/ProductTest.php

# Функциональные тесты
php bin/phpunit tests/Controller/ProductControllerTest.php

# Все тесты
php bin/phpunit
```

## Обработка ошибок

API возвращает стандартные HTTP коды статуса:

- **200 OK** - успешный запрос
- **201 Created** - ресурс успешно создан
- **400 Bad Request** - ошибка валидации
- **404 Not Found** - ресурс не найден
- **500 Internal Server Error** - внутренняя ошибка сервера

Пример ошибки валидации:
```json
{
    "errors": "name: This value should not be blank."
}
```

## Лицензия

MIT

## Автор

Created by SF ТЯПляпивпродакшн Team)
