<?php

declare(strict_types=1);

$appConfig = require dirname(__DIR__) . '/config/app.php';
$dbConfig = require dirname(__DIR__) . '/config/database.php';

date_default_timezone_set($appConfig['timezone']);

if ($appConfig['debug']) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
    ini_set('display_errors', '0');
}

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Router.php';
require_once __DIR__ . '/Auth.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/Services/Schema.php';
require_once __DIR__ . '/Services/UserService.php';
require_once __DIR__ . '/Services/ProjectService.php';
require_once __DIR__ . '/Services/QuestionService.php';
require_once __DIR__ . '/Services/SourceService.php';
require_once __DIR__ . '/Services/ClaimService.php';
require_once __DIR__ . '/Services/QuoteService.php';
require_once __DIR__ . '/Services/EvidenceService.php';
require_once __DIR__ . '/Services/ConclusionService.php';
require_once __DIR__ . '/Services/DossierService.php';
require_once __DIR__ . '/Services/StatsService.php';
require_once __DIR__ . '/Services/ChartSvg.php';

foreach ([
    'HomeController',
    'AuthController',
    'DashboardController',
    'ProjectController',
    'QuestionController',
    'SourceController',
    'ClaimController',
    'QuoteController',
    'EvidenceController',
    'ConclusionController',
    'DossierController',
    'SearchController',
    'SettingsController',
] as $controller) {
    require_once __DIR__ . '/Controllers/' . $controller . '.php';
}

if (PHP_SAPI !== 'cli') {
    Auth::startSession($appConfig['session_name']);
    if (!headers_sent()) {
        header('X-Frame-Options: SAMEORIGIN');
        header('X-Content-Type-Options: nosniff');
        header('Referrer-Policy: strict-origin-when-cross-origin');
    }
}

try {
    Database::connect($dbConfig);
    if (PHP_SAPI !== 'cli') {
        Schema::ensure();
    }
} catch (Throwable $e) {
    if (PHP_SAPI === 'cli') {
        fwrite(STDERR, 'DB error: ' . $e->getMessage() . PHP_EOL);
        exit(1);
    }
    http_response_code(503);
    echo '<!DOCTYPE html><html lang="es"><head><meta charset="utf-8"><title>ResearchForge</title></head>';
    echo '<body style="font-family:Georgia,serif;background:#F3EFE6;color:#1A2332;padding:2rem">';
    echo '<h1>ResearchForge</h1><p>No se pudo conectar a la base.</p>';
    if ($appConfig['debug']) {
        echo '<pre>' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</pre>';
        echo '<p>Abrí <code>install.php</code> o configurá <code>.env</code>.</p>';
    }
    echo '</body></html>';
    exit;
}

return [
    'app' => $appConfig,
    'db' => $dbConfig,
];
