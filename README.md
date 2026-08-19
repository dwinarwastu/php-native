# PHP Native

> Production-grade RESTful API backend — built to show real-world PHP Native architecture.

Built with **PHP 8**, **PDO MySQL**, and **Clean Architecture** principles. Exposes a lightweight API protected by API Key authentication.

---

## Tech Stack

| Layer | Technology |
|---|---|
| Language | PHP 8.0+ |
| Architecture | Clean Architecture (Domain, Application, Infrastructure) |
| Database | MySQL / MariaDB (PDO) |
| Authentication | API Key Middleware (`X-API-KEY`) |
| Package Manager | Composer (PSR-4 Autoloading) |

---

## How It Works

1. **Authenticate** — Client or internal service calls an API endpoint with the `X-API-KEY` HTTP header
2. **Dispatch** — `public/index.php` routes the incoming HTTP request through `Router`
3. **Evaluate** — `ApiKeyMiddleware` invokes `ValidateApiKeyUseCase` to verify the key against `config/app.php`
4. **Response** — Returns standardized JSON with `success: true/false`, HTTP status code, and payload or error message

---

## Architecture Layers

The codebase strictly adheres to Clean Architecture principles:

- **Domain Layer (`src/Domain`)**: Enterprise business entities and repository abstractions without external dependencies.
- **Application Layer (`src/Application`)**: Use cases orchestrating application workflow logic and domain exceptions.
- **Infrastructure Layer (`src/Infrastructure`)**: Technical implementations including HTTP router, controllers, middleware, logger, and database connections.

---

## Project Structure

```
config/
├── app.php
├── database.php
└── debug.php
public/
├── .htaccess
└── index.php
src/
├── Domain/
├── Application/
│   ├── Exceptions/
│   └── UseCases/
│       └── Auth/
└── Infrastructure/
    ├── Database/
    ├── Http/
    │   ├── Controllers/
    │   ├── Middlewares/
    │   ├── Request.php
    │   ├── Response.php
    │   └── Router.php
    └── Logger/
```

---

## Getting Started

### Prerequisites

- PHP 7.4 or 8.0+
- Composer

### Run Locally

```bash
# Install dependencies
composer install

# Start built-in PHP development server
php -S localhost:8000 -t public
```

---

## Configuration Variables

### Application Config (`config/app.php`)

| Field | Description | Default |
|---|---|---|
| `name` | Application name | `PHP Native` |
| `env` | Environment | `development` |
| `version` | Application version | `1.0.0` |
| `auth.header` | HTTP header name for API Key | `HTTP_X_API_KEY` |
| `auth.api_key` | 32-character API Key | `c3a08deba2285418da7cc14c1b22efec` |
| `cors.allowed_origins` | CORS allowed origins | `*` |
| `cors.allowed_methods` | CORS allowed methods | `GET, POST, PUT, DELETE, OPTIONS` |

### Database Config (`config/database.php`)

| Field | Description | Default |
|---|---|---|
| `driver` | Database driver | `mysql` |
| `host` | Database host | `localhost` |
| `database` | Database name | `php_native` |
| `username` | Database username | `root` |
| `password` | Database password | `""` |

---

## API Reference

### Health Check

```http
GET /health
GET /api/health
```

**Response — Success (`200 OK`)**

```json
{
  "success": true,
  "message": "API Service is operational",
  "data": {
    "status": "UP",
    "timestamp": "2026-08-19T10:00:00Z",
    "app": "PHP Native",
    "version": "1.0.0"
  }
}
```

---

### Protected Endpoint Example

```http
GET /api/your-endpoint
X-API-KEY: c3a08deba2285418da7cc14c1b22efec
```

**Response — Unauthorized (`401 Unauthorized`)**

```json
{
  "success": false,
  "error": "Unauthorized: Invalid or missing API Key"
}
```

---

## License

This project is licensed under the [MIT License](LICENSE).
