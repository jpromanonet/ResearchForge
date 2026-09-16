<?php

declare(strict_types=1);

final class ClaimController
{
    public static function create(string $projectId): void
    {
        Auth::requireLogin();
        $project = ProjectService::requireOwned((int) $projectId, Auth::id());
        view('claims/form', [
            'title' => 'Nueva afirmación',
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
            ClaimService::create((int) $projectId, $_POST);
            flash('success', 'Afirmación creada.');
            redirect('/investigaciones/' . $projectId . '?tab=afirmaciones');
        } catch (Throwable $e) {
            flash('error', $e->getMessage());
            redirect('/investigaciones/' . $projectId . '/afirmaciones/nueva');
        }
    }

    public static function edit(string $projectId, string $id): void
    {
        Auth::requireLogin();
        $project = ProjectService::requireOwned((int) $projectId, Auth::id());
        $item = ClaimService::findInProject((int) $id, (int) $projectId);
        if (!$item) {
            flash('error', 'Afirmación no encontrada.');
            redirect('/investigaciones/' . $projectId . '?tab=afirmaciones');
        }
        view('claims/form', [
            'title' => 'Editar afirmación',
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
            ClaimService::update((int) $id, (int) $projectId, $_POST);
            flash('success', 'Afirmación actualizada.');
            redirect('/investigaciones/' . $projectId . '?tab=afirmaciones');
        } catch (Throwable $e) {
            flash('error', $e->getMessage());
            redirect('/investigaciones/' . $projectId . '/afirmaciones/' . $id . '/editar');
        }
    }

    public static function destroy(string $projectId, string $id): void
    {
        Auth::requireLogin();
        require_csrf();
        ProjectService::requireOwned((int) $projectId, Auth::id());
        ClaimService::delete((int) $id, (int) $projectId);
        flash('success', 'Afirmación eliminada.');
        redirect('/investigaciones/' . $projectId . '?tab=afirmaciones');
    }
}
