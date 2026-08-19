<?php

declare(strict_types=1);

namespace App\Infrastructure\Database;

use App\Infrastructure\Logger\Logger;
use PDO;
use PDOException;

class Database
{
    private static ?PDO $instance = null;
    private static ?Logger $logger = null;

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $config = require __DIR__ . '/../../../config/database.php';
            self::$logger = new Logger();

            try {
                $driver = strtolower((string)($config['driver'] ?? 'pgsql'));
                $host = (string)($config['host'] ?? 'localhost');
                $port = (int)($config['port'] ?? ($driver === 'pgsql' ? 5432 : 3306));
                $dbname = (string)($config['database'] ?? 'php_native');
                $charset = (string)($config['charset'] ?? 'utf8');

                if ($driver === 'pgsql') {
                    $dsn = sprintf("pgsql:host=%s;port=%d;dbname=%s", $host, $port, $dbname);
                } else {
                    $dsn = sprintf("mysql:host=%s;port=%d;dbname=%s;charset=%s", $host, $port, $dbname, $charset);
                }

                self::$instance = new PDO(
                    $dsn,
                    (string)($config['username'] ?? ''),
                    (string)($config['password'] ?? ''),
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false
                    ]
                );
            } catch (PDOException $e) {
                self::$logger->error('Database connection failed', [
                    'error' => $e->getMessage()
                ]);
                throw $e;
            }
        }

        return self::$instance;
    }
}
