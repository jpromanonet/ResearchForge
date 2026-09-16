<?php

declare(strict_types=1);

final class QuoteController
{
    public static function create(string $projectId): void
    {
        Auth::requireLogin();
        $project = ProjectService::requireOwned((int) $projectId, Auth::id());
        view('quotes/form', [
            'title' => 'Nueva cita',
            'project' => $project,
            'item' => null,
            'sources' => SourceService::forProject((int) $projectId),
            'claims' => ClaimService::forProject((int) $projectId),
        ]);
    }

    public static function store(string $projectId): void
    {
        Auth::requireLogin();
        require_csrf();
        ProjectService::requireOwned((int) $projectId, Auth::id());
        try {
            QuoteService::create((int) $projectId, $_POST);
            flash('success', 'Cita guardada.');
            redirect('/investigaciones/' . $projectId . '?tab=citas');
        } catch (Throwable $e) {
            flash('error', $e->getMessage());
            redirect('/investigaciones/' . $projectId . '/citas/nueva');
        }
    }

    public static function edit(string $projectId, string $id): void
    {
        Auth::requireLogin();
        $project = ProjectService::requireOwned((int) $projectId, Auth::id());
        $item = QuoteService::findInProject((int) $id, (int) $projectId);
        if (!$item) {
            flash('error', 'Cita no encontrada.');
            redirect('/investigaciones/' . $projectId . '?tab=citas');
        }
        view('quotes/form', [
            'title' => 'Editar cita',
            'project' => $project,
            'item' => $item,
            'sources' => SourceService::forProject((int) $projectId),
            'claims' => ClaimService::forProject((int) $projectId),
        ]);
    }

    public static function update(string $projectId, string $id): void
    {
        Auth::requireLogin();
        require_csrf();
        ProjectService::requireOwned((int) $projectId, Auth::id());
        try {
            QuoteService::update((int) $id, (int) $projectId, $_POST);
            flash('success', 'Cita actualizada.');
            redirect('/investigaciones/' . $projectId . '?tab=citas');
        } catch (Throwable $e) {
            flash('error', $e->getMessage());
            redirect('/investigaciones/' . $projectId . '/citas/' . $id . '/editar');
        }
    }

    public static function destroy(string $projectId, string $id): void
    {
        Auth::requireLogin();
        require_csrf();
        ProjectService::requireOwned((int) $projectId, Auth::id());
        QuoteService::delete((int) $id, (int) $projectId);
        flash('success', 'Cita eliminada.');
        redirect('/investigaciones/' . $projectId . '?tab=citas');
    }
}
