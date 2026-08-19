<?php

declare(strict_types=1);

namespace App\Application\UseCases\Auth;

class ValidateApiKeyUseCase
{
    private string $apiKey;

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function execute(string $providedKey): bool
    {
        if (empty(trim($providedKey)) || empty($this->apiKey)) {
            return false;
        }

        return hash_equals($this->apiKey, $providedKey);
    }
}
