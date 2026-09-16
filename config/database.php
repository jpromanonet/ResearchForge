<?php

declare(strict_types=1);

require_once __DIR__ . '/env.php';

return [
    'host' => rf_env('DB_HOST', '127.0.0.1') ?? '127.0.0.1',
    'port' => (int) (rf_env('DB_PORT', '3306') ?? '3306'),
    'name' => rf_env('DB_NAME', 'researchforge') ?? 'researchforge',
    'user' => rf_env('DB_USER', 'root') ?? 'root',
    'pass' => rf_env('DB_PASS', '') ?? '',
    'charset' => 'utf8mb4',
];
