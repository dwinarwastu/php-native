# PHP Native

> Production-grade RESTful API backend — built to show real-world PHP Native architecture.

Built with **PHP 8**, **PostgreSQL / MySQL**, and **Clean Architecture** principles. Exposes a lightweight API protected by API Key authentication.

---

## Tech Stack

| Layer | Technology |
|---|---|
| Language | PHP 8.0+ |
| Architecture | Clean Architecture (Domain, Application, Infrastructure) |
| Database | PostgreSQL / MySQL (PDO) |
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
database/
└── surveys.sql
public/
├── .htaccess
└── index.php
src/
├── Domain/
│   ├── Entities/
│   │   └── Survey.php
│   └── Repositories/
│       └── SurveyRepositoryInterface.php
├── Application/
│   ├── Exceptions/
│   └── UseCases/
│       ├── Auth/
│       └── Survey/
│           └── GetSurveysUseCase.php
└── Infrastructure/
    ├── Database/
    ├── Http/
    │   ├── Controllers/
    │   │   ├── HealthController.php
    │   │   └── SurveyController.php
    │   ├── Middlewares/
    │   ├── Request.php
    │   ├── Response.php
    │   └── Router.php
    ├── Logger/
    └── Persistence/
        └── PDOSurveyRepository.php
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

## API Reference

### Health Check

```http
GET /health
GET /api/health
```

---

### Get Surveys (Paginated & Filtered)

```http
GET /api/surveys?page=1&limit=10&date=19&month=8&year=2026
X-API-KEY: c3a08deba2285418da7cc14c1b22efec
```

| Query Param | Type | Required | Description |
|---|---|---|---|
| `page` | `int` | No | Page number (default: `1`) |
| `limit` | `int` | No | Items per page (default: `10`, max: `100`) |
| `date` | `int` | No | Filter by day of month (`1-31`) |
| `month` | `int` | No | Filter by month (`1-12`) |
| `year` | `int` | No | Filter by year (`YYYY`) |

**Response — Success (`200 OK`)**

```json
{
  "success": true,
  "message": "Surveys retrieved successfully",
  "data": {
    "items": [
      {
        "id": 1,
        "username": "johndoe",
        "visit": "First Time",
        "site": "Main Office",
        "resource": "Website",
        "nation": "Indonesia",
        "bali": "Yes",
        "visit_time": "Morning",
        "news": "Social Media",
        "datetime": "2026-08-19 10:00:00"
      }
    ],
    "pagination": {
      "page": 1,
      "limit": 10,
      "total_items": 1,
      "total_pages": 1
    }
  }
}
```

---

## License

This project is licensed under the [MIT License](LICENSE).
