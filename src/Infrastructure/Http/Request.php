<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

class Request
{
    private array $body;
    private array $query;
    private array $headers;

    public function __construct()
    {
        $this->query = $_GET;
        $this->headers = getallheaders() ?: [];

        $rawBody = file_get_contents('php://input');
        $json = json_decode($rawBody ?: '', true);

        $this->body = is_array($json) ? $json : $_POST;
    }

    public function getBody(): array
    {
        return $this->body;
    }

    public function get(string $key, $default = null)
    {
        return $this->body[$key] ?? $this->query[$key] ?? $default;
    }

    public function getHeader(string $key, ?string $default = null): ?string
    {
        foreach ($this->headers as $headerKey => $value) {
            if (strcasecmp($headerKey, $key) === 0) {
                return $value;
            }
        }

        $serverKey = 'HTTP_' . strtoupper(str_replace('-', '_', $key));
        if (isset($_SERVER[$serverKey])) {
            return $_SERVER[$serverKey];
        }

        return $default;
    }

    public function getMethod(): string
    {
        return $_SERVER['REQUEST_METHOD'] ?? 'GET';
    }

    public function getPath(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        return parse_url($uri, PHP_URL_PATH) ?? '/';
    }
}
