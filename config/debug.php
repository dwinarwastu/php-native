<?php

return [
    'debug_mode' => true,
    'logging' => [
        'enabled' => true,
        'level' => 'DEBUG',
        'file' => dirname(__DIR__) . '/logs/app.log',
        'display_errors' => true,
        'display_startup_errors' => true,
        'error_reporting' => E_ALL,
        'max_file_size' => 10,
        'max_files' => 5
    ]
];
