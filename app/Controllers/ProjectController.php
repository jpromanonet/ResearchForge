<?php

declare(strict_types=1);

final class ProjectController
{
    public static function index(): void
    {
        Auth::requireLogin();
        $status = (string) ($_GET['status'] ?? '');
        view('projects/index', [
            'title' => 'Investigaciones',
            'projects' => ProjectService::forUser(Auth::id(), $status !== '' ? $status : null),
            'status' => $status,
        ]);
    }

    public static function create(): void
    {
        Auth::requireLogin();
        view('projects/form', [
            'title' => 'Nueva investigación',
            'project' => null,
        ]);
    }

    public static function store(): void
    {
        Auth::requireLogin();
        require_csrf();
        try {
            $id = ProjectService::create(Auth::id(), $_POST);
            flash('success', 'Investigación creada.');
            redirect('/investigaciones/' . $id);
        } catch (Throwable $e) {
            flash('error', $e->getMessage());
            redirect('/investigaciones/nueva');
        }
    }

    public static function show(string $id): void
    {
        Auth::requireLogin();
        $project = ProjectService::requireOwned((int) $id, Auth::id());
        $tab = (string) ($_GET['tab'] ?? 'overview');
        $allowed = ['overview', 'metricas', 'preguntas', 'fuentes', 'afirmaciones', 'citas', 'evidencias', 'conclusiones', 'dossiers'];
        if (!in_array($tab, $allowed, true)) {
            $tab = 'overview';
        }
        view('projects/show', [
            'title' => $project['title'],
            'project' => $project,
            'tab' => $tab,
            'bundle' => ProjectService::bundle((int) $id),
            'metrics' => StatsService::forProject((int) $id),
        ]);
    }

    public static function edit(string $id): void
    {
        Auth::requireLogin();
        $project = ProjectService::requireOwned((int) $id, Auth::id());
        view('projects/form', [
            'title' => 'Editar investigación',
            'project' => $project,
        ]);
    }

    public static function update(string $id): void
    {
        Auth::requireLogin();
        require_csrf();
        try {
            ProjectService::update((int) $id, Auth::id(), $_POST);
            flash('success', 'Investigación actualizada.');
            redirect('/investigaciones/' . $id);
        } catch (Throwable $e) {
            flash('error', $e->getMessage());
            redirect('/investigaciones/' . $id . '/editar');
        }
    }

    public static function destroy(string $id): void
    {
        Auth::requireLogin();
        require_csrf();
        ProjectService::delete((int) $id, Auth::id());
        flash('success', 'Investigación eliminada.');
        redirect('/investigaciones');
    }

    public static function exportPdf(string $id): void
    {
        Auth::requireLogin();
        $project = ProjectService::requireOwned((int) $id, Auth::id());
        $autoPrint = isset($_GET['print']);
        view('projects/pdf', [
            'title' => 'PDF · ' . $project['title'],
            'project' => $project,
            'bundle' => ProjectService::bundle((int) $id),
            'metrics' => StatsService::forProject((int) $id),
            'user' => Auth::user(),
            'autoPrint' => $autoPrint,
            'generatedAt' => date('d/m/Y H:i'),
        ], 'layouts/print');
    }
}
