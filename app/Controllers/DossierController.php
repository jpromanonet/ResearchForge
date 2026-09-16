<?php

declare(strict_types=1);

final class DossierController
{
    public static function create(string $projectId): void
    {
        Auth::requireLogin();
        $project = ProjectService::requireOwned((int) $projectId, Auth::id());
        $bundle = ProjectService::bundle((int) $projectId);
        view('dossiers/form', [
            'title' => 'Nuevo dossier',
            'project' => $project,
            'item' => null,
            'bundle' => $bundle,
            'selected' => [
                'questions' => array_column($bundle['questions'], 'id'),
                'sources' => array_column($bundle['sources'], 'id'),
                'claims' => array_column($bundle['claims'], 'id'),
                'quotes' => array_column($bundle['quotes'], 'id'),
                'evidence' => array_column($bundle['evidence'], 'id'),
                'conclusions' => array_column($bundle['conclusions'], 'id'),
            ],
        ]);
    }

    public static function store(string $projectId): void
    {
        Auth::requireLogin();
        require_csrf();
        ProjectService::requireOwned((int) $projectId, Auth::id());
        try {
            $id = DossierService::create((int) $projectId, $_POST);
            flash('success', 'Dossier creado.');
            redirect('/investigaciones/' . $projectId . '/dossiers/' . $id);
        } catch (Throwable $e) {
            flash('error', $e->getMessage());
            redirect('/investigaciones/' . $projectId . '/dossiers/nuevo');
        }
    }

    public static function show(string $projectId, string $id): void
    {
        Auth::requireLogin();
        $project = ProjectService::requireOwned((int) $projectId, Auth::id());
        $assembled = DossierService::assemble((int) $id, (int) $projectId);
        if (!$assembled) {
            flash('error', 'Dossier no encontrado.');
            redirect('/investigaciones/' . $projectId . '?tab=dossiers');
        }
        view('dossiers/show', [
            'title' => $assembled['dossier']['title'],
            'project' => $project,
            'assembled' => $assembled,
        ]);
    }

    public static function edit(string $projectId, string $id): void
    {
        Auth::requireLogin();
        $project = ProjectService::requireOwned((int) $projectId, Auth::id());
        $item = DossierService::findInProject((int) $id, (int) $projectId);
        if (!$item) {
            flash('error', 'Dossier no encontrado.');
            redirect('/investigaciones/' . $projectId . '?tab=dossiers');
        }
        $bundle = ProjectService::bundle((int) $projectId);
        $selected = DossierService::selectedMap(DossierService::items((int) $id));
        view('dossiers/form', [
            'title' => 'Editar dossier',
            'project' => $project,
            'item' => $item,
            'bundle' => $bundle,
            'selected' => $selected,
        ]);
    }

    public static function update(string $projectId, string $id): void
    {
        Auth::requireLogin();
        require_csrf();
        ProjectService::requireOwned((int) $projectId, Auth::id());
        try {
            DossierService::update((int) $id, (int) $projectId, $_POST);
            flash('success', 'Dossier actualizado.');
            redirect('/investigaciones/' . $projectId . '/dossiers/' . $id);
        } catch (Throwable $e) {
            flash('error', $e->getMessage());
            redirect('/investigaciones/' . $projectId . '/dossiers/' . $id . '/editar');
        }
    }

    public static function destroy(string $projectId, string $id): void
    {
        Auth::requireLogin();
        require_csrf();
        ProjectService::requireOwned((int) $projectId, Auth::id());
        DossierService::delete((int) $id, (int) $projectId);
        flash('success', 'Dossier eliminado.');
        redirect('/investigaciones/' . $projectId . '?tab=dossiers');
    }

    public static function export(string $projectId, string $id): void
    {
        Auth::requireLogin();
        ProjectService::requireOwned((int) $projectId, Auth::id());
        $assembled = DossierService::assemble((int) $id, (int) $projectId);
        if (!$assembled) {
            flash('error', 'Dossier no encontrado.');
            redirect('/investigaciones/' . $projectId . '?tab=dossiers');
        }

        $format = strtolower((string) ($_GET['format'] ?? 'md'));
        $slug = preg_replace('/[^a-z0-9]+/i', '-', (string) $assembled['dossier']['title']) ?: 'dossier';
        $slug = trim($slug, '-');

        if ($format === 'json') {
            header('Content-Type: application/json; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $slug . '.json"');
            echo DossierService::toJson($assembled);
            exit;
        }
        if ($format === 'html') {
            header('Content-Type: text/html; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $slug . '.html"');
            echo DossierService::toHtml($assembled);
            exit;
        }

        header('Content-Type: text/markdown; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $slug . '.md"');
        echo DossierService::toMarkdown($assembled);
        exit;
    }
}
