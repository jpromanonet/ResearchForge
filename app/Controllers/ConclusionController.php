<?php

declare(strict_types=1);

final class ConclusionController
{
    public static function create(string $projectId): void
    {
        Auth::requireLogin();
        $project = ProjectService::requireOwned((int) $projectId, Auth::id());
        view('conclusions/form', [
            'title' => 'Nueva conclusión',
            'project' => $project,
            'item' => null,
            'questions' => QuestionService::forProject((int) $projectId),
        ]);
    }

    public static function store(string $projectId): void
    {
        Auth::requireLogin();
        require_csrf();
        ProjectService::requireOwned((int) $projectId, Auth::id());
        try {
            ConclusionService::create((int) $projectId, $_POST);
            flash('success', 'Conclusión guardada.');
            redirect('/investigaciones/' . $projectId . '?tab=conclusiones');
        } catch (Throwable $e) {
            flash('error', $e->getMessage());
            redirect('/investigaciones/' . $projectId . '/conclusiones/nueva');
        }
    }

    public static function edit(string $projectId, string $id): void
    {
        Auth::requireLogin();
        $project = ProjectService::requireOwned((int) $projectId, Auth::id());
        $item = ConclusionService::findInProject((int) $id, (int) $projectId);
        if (!$item) {
            flash('error', 'Conclusión no encontrada.');
            redirect('/investigaciones/' . $projectId . '?tab=conclusiones');
        }
        view('conclusions/form', [
            'title' => 'Editar conclusión',
            'project' => $project,
            'item' => $item,
            'questions' => QuestionService::forProject((int) $projectId),
        ]);
    }

    public static function update(string $projectId, string $id): void
    {
        Auth::requireLogin();
        require_csrf();
        ProjectService::requireOwned((int) $projectId, Auth::id());
        try {
            ConclusionService::update((int) $id, (int) $projectId, $_POST);
            flash('success', 'Conclusión actualizada.');
            redirect('/investigaciones/' . $projectId . '?tab=conclusiones');
        } catch (Throwable $e) {
            flash('error', $e->getMessage());
            redirect('/investigaciones/' . $projectId . '/conclusiones/' . $id . '/editar');
        }
    }

    public static function destroy(string $projectId, string $id): void
    {
        Auth::requireLogin();
        require_csrf();
        ProjectService::requireOwned((int) $projectId, Auth::id());
        ConclusionService::delete((int) $id, (int) $projectId);
        flash('success', 'Conclusión eliminada.');
        redirect('/investigaciones/' . $projectId . '?tab=conclusiones');
    }
}
