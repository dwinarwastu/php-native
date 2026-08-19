# PHP Native RESTful API Backend

A lightweight PHP Native RESTful API backend service implementing Clean Architecture principles. Designed for secure server-to-server and client communication using API Key authentication.

## Table of Contents
- [Features](#features)
- [Project Structure](#project-structure)
- [System Requirements](#system-requirements)
- [Installation and Configuration](#installation-and-configuration)
- [API Key Authentication](#api-key-authentication)
- [API Documentation](#api-documentation)
- [Running the Application](#running-the-application)
- [Extending the Codebase](#extending-the-codebase)
- [License](#license)

## Features

- **Clean Architecture**: Clear separation between Domain, Application, and Infrastructure layers.
- **Lightweight & Fast**: Built with native PHP without heavy framework overhead.
- **API Key Authentication**: Simple, secure request validation via the `X-API-KEY` HTTP header.
- **Standardized Response**: Unified JSON response structure across all endpoints (`success`, `message`, `data`, `error`).
- **CORS Support**: Configurable Cross-Origin Resource Sharing for seamless client integration.

## Project Structure

```
├── config/
│   ├── app.php
│   ├── database.php
│   └── debug.php
├── public/
│   ├── .htaccess
│   └── index.php
├── src/
│   ├── Domain/
│   ├── Application/
│   │   ├── Exceptions/
│   │   └── UseCases/
│   │       └── Auth/
│   └── Infrastructure/
│       ├── Database/
│       ├── Http/
│       │   ├── Controllers/
│       │   ├── Middlewares/
│       │   ├── Request.php
│       │   ├── Response.php
│       │   └── Router.php
│       └── Logger/
├── composer.json
└── README.md
```

### Architecture Layer Overview
1. **Domain Layer (`src/Domain`)**: Enterprise business entities and repository abstractions without external dependencies.
2. **Application Layer (`src/Application`)**: Use cases orchestrating application workflow logic.
3. **Infrastructure Layer (`src/Infrastructure`)**: Technical implementations including HTTP router, controllers, middleware, and database connections.

## System Requirements

- PHP 7.4 or 8.0+
- Composer

## Installation and Configuration

### 1. Install Dependencies

```bash
git clone <repository-url>
cd php-native
composer install
```

### 2. Configuration

Set up application settings in `config/app.php`:

```php
return [
    'name' => 'PHP Native',
    'env' => 'development',
    'version' => '1.0.0',

    'auth' => [
        'header' => 'HTTP_X_API_KEY',
        'api_key' => 'c3a08deba2285418da7cc14c1b22efec',
    ],

    'cors' => [
        'allowed_origins' => '*',
        'allowed_methods' => 'GET, POST, PUT, DELETE, OPTIONS',
        'allowed_headers' => 'Content-Type, X-API-Key, Authorization',
    ],
];
```

## API Key Authentication

Protected endpoints require the `X-API-KEY` header in HTTP requests:

```http
GET /health HTTP/1.1
Host: localhost:8000
X-API-KEY: c3a08deba2285418da7cc14c1b22efec
```

## API Documentation

| Method | Endpoint | Auth Required | Description |
| :--- | :--- | :---: | :--- |
| `GET` | `/health` | No | Health check status endpoint |
| `GET` | `/api/health` | No | Health check status endpoint |

### API Responses

#### Success Response (`200 OK`)
```json
{
  "success": true,
  "message": "API Service is operational",
  "data": {
    "status": "UP",
    "timestamp": "2026-08-19 10:00:00",
    "app": "PHP Native",
    "version": "1.0.0"
  }
}
```

#### Unauthorized Response (`401 Unauthorized`)
```json
{
  "success": false,
  "error": "Unauthorized: Invalid or missing API Key"
}
```

## Running the Application

Start the built-in PHP development server:

```bash
php -S localhost:8000 -t public
```

Test health check:

```bash
curl -i http://localhost:8000/api/health
```

## Extending the Codebase

To add new features (e.g., Products or Orders), follow these steps:

1. **Domain Layer**: Add Entity in `src/Domain/Entities/` and Repository Interface in `src/Domain/Repositories/`.
2. **Application Layer**: Add Use Case in `src/Application/UseCases/`.
3. **Infrastructure Layer**: Implement Repository in `src/Infrastructure/Persistence/` and Controller in `src/Infrastructure/Http/Controllers/`.
4. **Routing**: Register the new route in `public/index.php`.

## License

This project is licensed under the MIT License.
