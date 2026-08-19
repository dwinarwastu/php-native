<?php

declare(strict_types=1);

namespace App\Infrastructure\Logger;

use InvalidArgumentException;

class Logger
{
    private string $logFile;
    private const LOG_LEVELS = ['DEBUG', 'INFO', 'WARNING', 'ERROR'];

    public function __construct(?string $logFile = null)
    {
        $this->logFile = $logFile ?? dirname(__DIR__, 3) . '/logs/app.log';
        $this->ensureLogDirectoryExists();
    }

    private function ensureLogDirectoryExists(): void
    {
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }
    }

    public function debug(string $message, array $context = []): void
    {
        $this->log('DEBUG', $message, $context);
    }

    public function info(string $message, array $context = []): void
    {
        $this->log('INFO', $message, $context);
    }

    public function warning(string $message, array $context = []): void
    {
        $this->log('WARNING', $message, $context);
    }

    public function error(string $message, array $context = []): void
    {
        $this->log('ERROR', $message, $context);
    }

    private function log(string $level, string $message, array $context = []): void
    {
        if (!in_array($level, self::LOG_LEVELS, true)) {
            throw new InvalidArgumentException('Invalid log level');
        }

        $timestamp = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? ' ' . json_encode($context, JSON_PRETTY_PRINT) : '';
        $logEntry = sprintf(
            "[%s] %s: %s%s\n",
            $timestamp,
            $level,
            $message,
            $contextStr
        );

        file_put_contents($this->logFile, $logEntry, FILE_APPEND);
    }

    public function logRequest(): void
    {
        $requestData = [
            'method' => $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN',
            'uri' => $_SERVER['REQUEST_URI'] ?? 'UNKNOWN',
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
            'headers' => getallheaders(),
            'query' => $_GET,
            'body' => $_POST,
        ];

        $this->info('HTTP Request', $requestData);
    }

    public function logQuery(string $query, array $params = []): void
    {
        $this->debug('SQL Query', [
            'query' => $query,
            'params' => $params
        ]);
    }
}
