<?php

declare(strict_types=1);

require_once __DIR__ . '/env.php';

return [
    'name' => rf_env('APP_NAME', 'ResearchForge') ?? 'ResearchForge',
    'env' => rf_env('APP_ENV', 'local') ?? 'local',
    'debug' => filter_var(rf_env('APP_DEBUG', 'true'), FILTER_VALIDATE_BOOLEAN),
    'url' => rf_env('APP_URL', '') ?? '',
    'timezone' => 'America/Argentina/Buenos_Aires',
    'session_name' => 'researchforge_session',
    'session_lifetime' => 86400,
    'session_idle' => 86400,
    'version' => '0.1.0',
];
