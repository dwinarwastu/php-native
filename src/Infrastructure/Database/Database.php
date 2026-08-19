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
                $dsn = sprintf(
                    "%s:host=%s;dbname=%s;charset=%s",
                    $config['driver'],
                    $config['host'],
                    $config['database'],
                    $config['charset']
                );

                self::$instance = new PDO(
                    $dsn,
                    $config['username'],
                    $config['password'],
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
