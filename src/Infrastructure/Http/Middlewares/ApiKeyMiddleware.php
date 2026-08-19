<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Middlewares;

use App\Application\UseCases\Auth\ValidateApiKeyUseCase;
use App\Infrastructure\Http\Request;
use App\Infrastructure\Http\Response;

class ApiKeyMiddleware
{
    private ValidateApiKeyUseCase $validateApiKeyUseCase;

    public function __construct(ValidateApiKeyUseCase $validateApiKeyUseCase)
    {
        $this->validateApiKeyUseCase = $validateApiKeyUseCase;
    }

    public function handle(Request $request): bool
    {
        $apiKey = $request->getHeader('X-API-KEY');

        if (empty($apiKey) || !$this->validateApiKeyUseCase->execute($apiKey)) {
            Response::unauthorized('Invalid or missing API Key');
            return false;
        }

        return true;
    }
}
