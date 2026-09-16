<?php

declare(strict_types=1);

final class SourceController
{
    public static function create(string $projectId): void
    {
        Auth::requireLogin();
        $project = ProjectService::requireOwned((int) $projectId, Auth::id());
        view('sources/form', [
            'title' => 'Nueva fuente',
            'project' => $project,
            'item' => null,
        ]);
    }

    public static function store(string $projectId): void
    {
        Auth::requireLogin();
        require_csrf();
        ProjectService::requireOwned((int) $projectId, Auth::id());
        try {
            SourceService::create((int) $projectId, $_POST);
            flash('success', 'Fuente registrada.');
            redirect('/investigaciones/' . $projectId . '?tab=fuentes');
        } catch (Throwable $e) {
            flash('error', $e->getMessage());
            redirect('/investigaciones/' . $projectId . '/fuentes/nueva');
        }
    }

    public static function edit(string $projectId, string $id): void
    {
        Auth::requireLogin();
        $project = ProjectService::requireOwned((int) $projectId, Auth::id());
        $item = SourceService::findInProject((int) $id, (int) $projectId);
        if (!$item) {
            flash('error', 'Fuente no encontrada.');
            redirect('/investigaciones/' . $projectId . '?tab=fuentes');
        }
        view('sources/form', [
            'title' => 'Editar fuente',
            'project' => $project,
            'item' => $item,
        ]);
    }

    public static function update(string $projectId, string $id): void
    {
        Auth::requireLogin();
        require_csrf();
        ProjectService::requireOwned((int) $projectId, Auth::id());
        try {
            SourceService::update((int) $id, (int) $projectId, $_POST);
            flash('success', 'Fuente actualizada.');
            redirect('/investigaciones/' . $projectId . '?tab=fuentes');
        } catch (Throwable $e) {
            flash('error', $e->getMessage());
            redirect('/investigaciones/' . $projectId . '/fuentes/' . $id . '/editar');
        }
    }

    public static function destroy(string $projectId, string $id): void
    {
        Auth::requireLogin();
        require_csrf();
        ProjectService::requireOwned((int) $projectId, Auth::id());
        SourceService::delete((int) $id, (int) $projectId);
        flash('success', 'Fuente eliminada.');
        redirect('/investigaciones/' . $projectId . '?tab=fuentes');
    }
}
