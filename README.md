# laravel-api-starter

Production-ready Laravel 12 REST API with Repository Pattern, Service Layer, Sanctum auth, PostgreSQL, and Swagger docs.

## Stack

| Layer | Technology |
|-------|-----------|
| Framework | Laravel 12 + PHP 8.3 |
| Auth | Laravel Sanctum (token-based) |
| Database | PostgreSQL 17 + Eloquent |
| Pattern | Repository + Service Layer |
| API Docs | L5-Swagger (OpenAPI 3.0) |
| Testing | PHPUnit / Laravel Testing |
| Container | Docker + Docker Compose |
| CI | GitHub Actions |

## Architecture

```
app/
├── Contracts/Repositories/   # Interfaces
├── Http/
│   ├── Controllers/Api/      # Thin controllers, delegate to services
│   ├── Requests/             # Form requests with validation
│   ├── Resources/            # API Resources
│   └── Middleware/
├── Models/                   # Eloquent models
├── Repositories/             # Concrete DB implementations
├── Services/                 # Business logic (AuthService, UserService)
└── Providers/
    └── RepositoryServiceProvider.php
```

## Quick Start

### With Docker

```bash
git clone https://github.com/nanoizax/laravel-api-starter
cd laravel-api-starter
cp .env.example .env
docker compose up
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
```

API: http://localhost:8000
Swagger UI: http://localhost:8000/api/documentation

### Local

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

## Endpoints

### Auth
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | /api/auth/register | Register |
| POST | /api/auth/login | Login → Sanctum token |
| POST | /api/auth/logout | Revoke token |
| GET | /api/auth/me | Profile |

### Users
| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| GET | /api/users | Admin | List paginated |
| GET | /api/users/{id} | Admin | Get by ID |
| PUT | /api/users/{id} | User | Update |
| DELETE | /api/users/{id} | Admin | Delete |

## Testing

```bash
php artisan test
```

## License

MIT — Leandro Perez · SonhoLab · contacto@sonholab.com
