# IMPORTANT: It's a mid-level from php-dev on fast buld in old SF classical approach to show what old projects looks like inside MVP, or from ai-agents. Not for production level!

# ВАЖНО: Это проект middle-уровня, быстрой сборки, для наглядного отображения классических ошибок, с минимальным набором файлов, по старой классической схеме: все внутри жирных контроллеров - MVP. Не относится к уровню "продашн"


Here is a thorough breakdown of issues across every layer of the project:

Основные замечания, по разделам:

---

## 1. Architecture — No Layering

The controller does everything: parses raw JSON, mutates the entity, calls the ORM, and formats the response. A senior project separates concerns:

- **No Repository pattern** — `$this->em->getRepository(Product::class)->find($id)` is called inline in every action.  
- **No Service layer** — business logic (create, update, delete) belongs in a `ProductService`, not a controller.
- **No DTOs / Request objects** — input is parsed with raw `json_decode` directly in the controller.  

---

## 2. Controller — Concrete Bugs & Bad Practices

**Silent failure on invalid JSON body**
If the request body is not valid JSON, `json_decode` returns `null`, and `$data['name'] ?? ''` silently passes an empty string through to validation. There is no `json_last_error()` check. 

**Validation errors are a plain string, not structured JSON**
`(string) $errors` dumps a raw `ConstraintViolationList` text representation into the JSON body. A proper API returns a structured array of `{field, message}` objects. 

**DELETE returns `200 OK` with a body**
The correct HTTP semantics for a successful delete is `204 No Content` with no body. 

**No `GET /api/products` list endpoint**
There is no collection endpoint with pagination or filtering — a fundamental feature of any catalogue API.

**No global exception handler**
Unhandled exceptions (e.g., database errors) will leak Symfony's debug stack trace in `dev` mode or return a generic 500 with no structured JSON body.

---

## 3. Entity — Design Issues

**`status` should be a PHP 8.1 Backed Enum**
Using a plain `string` with `@Assert\Choice` is fragile. A `BackedEnum` (`active`/`inactive`) gives type safety, IDE autocompletion, and eliminates the need for the validator constraint. 

**`price` is typed as `?string` in PHP**
Doctrine maps `DECIMAL` to string, but exposing `string` in the domain model is confusing. At minimum, a `Money` value object or a `float`/`int` (cents) representation is expected at a senior level. 

**`setCreatedAt()` is public**
The field is set automatically via `#[ORM\PrePersist]`, so the public setter is a footgun — any caller can overwrite it. It should be removed or made private. 

**No `updatedAt` timestamp**
Any real catalogue needs to know when a product was last modified.

---

## 4. Docker & Infrastructure — Broken / Inconsistent

**`entrypoint.sh` waits for MySQL, but the app uses SQLite**
The script tries to connect to `mysql:host=db` — this is dead code that will always fail silently (`|| true`). The app uses SQLite and has no `db` service.

**`compose.override.yaml` references a PostgreSQL port**
The override exposes port `5432` for a `database` service that does not exist in `docker-compose.yml`. This is leftover scaffolding that was never cleaned up. 

**Dockerfile uses `php:8.3-cli` + PHP built-in server**
The built-in server is explicitly documented as not suitable for production. A production image should use `php:8.3-fpm` with Nginx as a separate service.

**`|| true` suppresses all Composer errors silently**  

**`docker-compose.yml` uses deprecated `version: '3.3'`**
Modern Docker Compose (v2+) ignores the `version` key entirely. 

**No Nginx in the actual compose file**
There is a `docker/nginx/` directory but it is never wired into `docker-compose.yml`. 

---

## 5. Testing — Gaps

**`phpunit.xml.dist` pins `SYMFONY_PHPUNIT_VERSION` to `10.5`**
But `composer.json` requires `phpunit/phpunit: ^13.0` — a direct version conflict.

**No code coverage configuration**
No `<coverage>` section in `phpunit.xml.dist`, no coverage thresholds enforced.

**No CI/CD pipeline**
There is no `.github/workflows/` or equivalent. A FAANG-level project runs tests, static analysis, and linting on every push.

---

## 6. Code Quality Tooling — Entirely Missing

`require-dev` has no static analysis or code style tools:  

| Tool | Purpose |
|---|---|
| `phpstan/phpstan` (level 8+) | Static analysis |
| `friendsofphp/php-cs-fixer` | Code style enforcement |
| `psalm/psalm` | Type-level analysis |

---

## 7. Security

- `APP_SECRET=change_this_secret_key_in_production` is committed to `.env` and checked into version control.  
- No authentication or authorization on any endpoint.
- No rate limiting.
- No CORS headers configured.

---

## 8. API Design

- No API versioning (`/api/v1/products`).
- Inconsistent error response shape: `{"error": "..."}` for 404 vs `{"errors": "plain text string"}` for validation. 
- No OpenAPI/Swagger documentation (e.g., `nelmio/api-doc-bundle`).
- No `Content-Type: application/json` enforcement on incoming requests.

---

## Summary Priority Order

| Priority | Issue |
|---|---|
| Critical | Broken Docker (`entrypoint.sh` MySQL check, `compose.override.yaml` ghost service) |
| Critical | `json_decode` null-safety + structured validation error response |
| High | `status` → PHP Enum; remove public `setCreatedAt` |
| High | Repository + Service layer separation |
| High | Add PHPStan + PHP CS Fixer + CI pipeline |
| Medium | `DELETE` → `204 No Content`; add `updatedAt`; add list endpoint |
| Medium | API versioning + OpenAPI docs |
| Low | Nginx in compose; `php-fpm` image; remove `version:` from compose |

### Citations

**File:** src/Controller/ProductController.php (L28-33)
```php
        $data = json_decode($request->getContent(), true);

        $product = new Product();
        $product->setName($data['name'] ?? '');
        $product->setPrice($data['price'] ?? '0');
        $product->setStatus($data['status'] ?? 'active');
```

**File:** src/Controller/ProductController.php (L36-38)
```php
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }
```

**File:** src/Controller/ProductController.php (L49-49)
```php
        $product = $this->em->getRepository(Product::class)->find($id);
```

**File:** src/Controller/ProductController.php (L52-52)
```php
            return $this->json(['error' => 'Product not found'], Response::HTTP_NOT_FOUND);
```

**File:** src/Controller/ProductController.php (L98-101)
```php
        $this->em->remove($product);
        $this->em->flush();

        return $this->json(['message' => 'Product deleted'], Response::HTTP_OK);
```

**File:** src/Entity/Product.php (L27-32)
```php
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Assert\NotBlank]
    #[Assert\Type(type: 'numeric')]
    #[Assert\GreaterThanOrEqual(value: 0)]
    #[Groups(['product'])]
    private ?string $price = null;
```

**File:** src/Entity/Product.php (L34-37)
```php
    #[ORM\Column(type: Types::STRING, length: 20)]
    #[Assert\Choice(choices: ['active', 'inactive'])]
    #[Groups(['product'])]
    private string $status = 'active';
```

**File:** src/Entity/Product.php (L95-100)
```php
    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }
```

**File:** docker/entrypoint.sh (L5-12)
```shellscript
for i in {1..30}; do
    if php -r "try { new PDO('mysql:host=db;dbname=catalogue', 'root', 'root'); exit(0); } catch (Exception \$e) { exit(1); }" 2>/dev/null; then
        echo "Database is ready!"
        break
    fi
    echo "Attempt $i/30: Database not ready, waiting..."
    sleep 2
done
```

**File:** compose.override.yaml (L1-7)
```yaml

services:
###> doctrine/doctrine-bundle ###
  database:
    ports:
      - "5432"
###< doctrine/doctrine-bundle ###
```

**File:** Dockerfile (L1-15)
```dockerfile
FROM php:8.3-cli

WORKDIR /var/www/html

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY composer.json symfony.lock ./

RUN composer install --ignore-platform-reqs --no-interaction || true

COPY . .

RUN composer dump-autoload --optimize || true

EXPOSE 8000
```

**File:** docker-compose.yml (L1-1)
```yaml
version: '3.3'
```

**File:** phpunit.xml.dist (L14-14)
```text
        <server name="SYMFONY_PHPUNIT_VERSION" value="10.5" />
```

**File:** composer.json (L26-33)
```json
    "require-dev": {
        "doctrine/doctrine-fixtures-bundle": "^3.4",
        "phpunit/phpunit": "^13.0",
        "symfony/browser-kit": "6.4.*",
        "symfony/css-selector": "6.4.*",
        "symfony/maker-bundle": "^1.0",
        "symfony/phpunit-bridge": "^6.4"
    },
```

**File:** .env (L2-2)
```text
APP_ENV=dev
```
