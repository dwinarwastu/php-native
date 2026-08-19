<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Application\UseCases\Auth\ValidateApiKeyUseCase;
use App\Application\UseCases\Survey\GetSurveysUseCase;
use App\Infrastructure\Database\Database;
use App\Infrastructure\Http\Controllers\HealthController;
use App\Infrastructure\Http\Controllers\SurveyController;
use App\Infrastructure\Http\Middlewares\ApiKeyMiddleware;
use App\Infrastructure\Http\Request;
use App\Infrastructure\Http\Response;
use App\Infrastructure\Http\Router;
use App\Infrastructure\Logger\Logger;
use App\Infrastructure\Persistence\PDOSurveyRepository;

$appConfig = require __DIR__ . '/../config/app.php';
$debugConfig = require __DIR__ . '/../config/debug.php';

if ($debugConfig['debug_mode'] ?? false) {
    error_reporting($debugConfig['logging']['error_reporting'] ?? E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

set_exception_handler(function (Throwable $exception) use ($debugConfig) {
    $logger = new Logger();
    $logger->error('Uncaught Exception: ' . $exception->getMessage(), [
        'file' => $exception->getFile(),
        'line' => $exception->getLine(),
        'trace' => $exception->getTraceAsString()
    ]);

    $details = ($debugConfig['debug_mode'] ?? false) ? [
        'file' => $exception->getFile(),
        'line' => $exception->getLine(),
    ] : [];

    Response::error('Internal Server Error: ' . $exception->getMessage(), 500, $details);
});

$request = new Request();
$router = new Router();

$getDatabaseConnection = function () {
    static $pdo = null;
    if ($pdo === null) {
        $pdo = Database::getInstance();
    }
    return $pdo;
};

$validateApiKeyUseCase = new ValidateApiKeyUseCase($appConfig['auth']['api_key'] ?? '');
$healthController = new HealthController();

$getSurveyController = function () use ($getDatabaseConnection) {
    $pdo = $getDatabaseConnection();
    $surveyRepository = new PDOSurveyRepository($pdo);
    $getSurveysUseCase = new GetSurveysUseCase($surveyRepository);
    return new SurveyController($getSurveysUseCase);
};

$requireApiKey = function (Request $request) use ($validateApiKeyUseCase) {
    $middleware = new ApiKeyMiddleware($validateApiKeyUseCase);
    return $middleware->handle($request);
};

$router->get('/health', [$healthController, 'check']);
$router->get('/api/health', [$healthController, 'check']);

$router->get('/api/surveys', function (Request $request) use ($getSurveyController) {
    $getSurveyController()->index($request);
}, [$requireApiKey]);

$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$basePath = dirname($scriptName);
if ($basePath === '/' || $basePath === '\\') {
    $basePath = '';
}

$router->dispatch($request, $basePath);
