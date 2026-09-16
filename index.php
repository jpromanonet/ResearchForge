<?php

declare(strict_types=1);

require __DIR__ . '/app/bootstrap.php';

$router = new Router();

$router->get('/', [HomeController::class, 'index']);

$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/registro', [AuthController::class, 'showRegister']);
$router->post('/registro', [AuthController::class, 'register']);
$router->post('/logout', [AuthController::class, 'logout']);

$router->get('/panel', [DashboardController::class, 'index']);
$router->get('/buscar', [SearchController::class, 'index']);
$router->get('/configuracion', [SettingsController::class, 'index']);
$router->post('/configuracion', [SettingsController::class, 'save']);
$router->post('/configuracion/password', [SettingsController::class, 'password']);
$router->post('/configuracion/avatar', [SettingsController::class, 'avatar']);
$router->post('/configuracion/avatar/eliminar', [SettingsController::class, 'avatarRemove']);
$router->post('/configuracion/tema', [SettingsController::class, 'theme']);

$router->get('/investigaciones', [ProjectController::class, 'index']);
$router->get('/investigaciones/nueva', [ProjectController::class, 'create']);
$router->post('/investigaciones', [ProjectController::class, 'store']);
$router->get('/investigaciones/{id}', [ProjectController::class, 'show']);
$router->get('/investigaciones/{id}/editar', [ProjectController::class, 'edit']);
$router->get('/investigaciones/{id}/exportar-pdf', [ProjectController::class, 'exportPdf']);
$router->post('/investigaciones/{id}', [ProjectController::class, 'update']);
$router->post('/investigaciones/{id}/eliminar', [ProjectController::class, 'destroy']);

$router->get('/investigaciones/{projectId}/preguntas/nueva', [QuestionController::class, 'create']);
$router->post('/investigaciones/{projectId}/preguntas', [QuestionController::class, 'store']);
$router->get('/investigaciones/{projectId}/preguntas/{id}/editar', [QuestionController::class, 'edit']);
$router->post('/investigaciones/{projectId}/preguntas/{id}', [QuestionController::class, 'update']);
$router->post('/investigaciones/{projectId}/preguntas/{id}/eliminar', [QuestionController::class, 'destroy']);

$router->get('/investigaciones/{projectId}/fuentes/nueva', [SourceController::class, 'create']);
$router->post('/investigaciones/{projectId}/fuentes', [SourceController::class, 'store']);
$router->get('/investigaciones/{projectId}/fuentes/{id}/editar', [SourceController::class, 'edit']);
$router->post('/investigaciones/{projectId}/fuentes/{id}', [SourceController::class, 'update']);
$router->post('/investigaciones/{projectId}/fuentes/{id}/eliminar', [SourceController::class, 'destroy']);

$router->get('/investigaciones/{projectId}/afirmaciones/nueva', [ClaimController::class, 'create']);
$router->post('/investigaciones/{projectId}/afirmaciones', [ClaimController::class, 'store']);
$router->get('/investigaciones/{projectId}/afirmaciones/{id}/editar', [ClaimController::class, 'edit']);
$router->post('/investigaciones/{projectId}/afirmaciones/{id}', [ClaimController::class, 'update']);
$router->post('/investigaciones/{projectId}/afirmaciones/{id}/eliminar', [ClaimController::class, 'destroy']);

$router->get('/investigaciones/{projectId}/citas/nueva', [QuoteController::class, 'create']);
$router->post('/investigaciones/{projectId}/citas', [QuoteController::class, 'store']);
$router->get('/investigaciones/{projectId}/citas/{id}/editar', [QuoteController::class, 'edit']);
$router->post('/investigaciones/{projectId}/citas/{id}', [QuoteController::class, 'update']);
$router->post('/investigaciones/{projectId}/citas/{id}/eliminar', [QuoteController::class, 'destroy']);

$router->get('/investigaciones/{projectId}/evidencias/nueva', [EvidenceController::class, 'create']);
$router->post('/investigaciones/{projectId}/evidencias', [EvidenceController::class, 'store']);
$router->get('/investigaciones/{projectId}/evidencias/{id}/editar', [EvidenceController::class, 'edit']);
$router->post('/investigaciones/{projectId}/evidencias/{id}', [EvidenceController::class, 'update']);
$router->post('/investigaciones/{projectId}/evidencias/{id}/eliminar', [EvidenceController::class, 'destroy']);

$router->get('/investigaciones/{projectId}/conclusiones/nueva', [ConclusionController::class, 'create']);
$router->post('/investigaciones/{projectId}/conclusiones', [ConclusionController::class, 'store']);
$router->get('/investigaciones/{projectId}/conclusiones/{id}/editar', [ConclusionController::class, 'edit']);
$router->post('/investigaciones/{projectId}/conclusiones/{id}', [ConclusionController::class, 'update']);
$router->post('/investigaciones/{projectId}/conclusiones/{id}/eliminar', [ConclusionController::class, 'destroy']);

$router->get('/investigaciones/{projectId}/dossiers/nuevo', [DossierController::class, 'create']);
$router->post('/investigaciones/{projectId}/dossiers', [DossierController::class, 'store']);
$router->get('/investigaciones/{projectId}/dossiers/{id}', [DossierController::class, 'show']);
$router->get('/investigaciones/{projectId}/dossiers/{id}/editar', [DossierController::class, 'edit']);
$router->post('/investigaciones/{projectId}/dossiers/{id}', [DossierController::class, 'update']);
$router->post('/investigaciones/{projectId}/dossiers/{id}/eliminar', [DossierController::class, 'destroy']);
$router->get('/investigaciones/{projectId}/dossiers/{id}/exportar', [DossierController::class, 'export']);

$router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $_SERVER['REQUEST_URI'] ?? '/');
