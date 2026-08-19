<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controllers;

use App\Infrastructure\Http\Request;
use App\Infrastructure\Http\Response;

class HealthController
{
    public function check(Request $request): void
    {
        $appConfig = require __DIR__ . '/../../../../config/app.php';

        Response::success([
            'status' => 'UP',
            'timestamp' => date('Y-m-d H:i:s'),
            'app' => $appConfig['name'] ?? 'PHP Native',
            'version' => $appConfig['version'] ?? '1.0.0'
        ], 'API Service is operational');
    }
}
