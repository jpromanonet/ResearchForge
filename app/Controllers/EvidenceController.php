<?php

declare(strict_types=1);

final class EvidenceController
{
    public static function create(string $projectId): void
    {
        Auth::requireLogin();
        $project = ProjectService::requireOwned((int) $projectId, Auth::id());
        view('evidence/form', [
            'title' => 'Nueva evidencia',
            'project' => $project,
            'item' => null,
            'claims' => ClaimService::forProject((int) $projectId),
            'sources' => SourceService::forProject((int) $projectId),
            'quotes' => QuoteService::forProject((int) $projectId),
        ]);
    }

    public static function store(string $projectId): void
    {
        Auth::requireLogin();
        require_csrf();
        ProjectService::requireOwned((int) $projectId, Auth::id());
        try {
            EvidenceService::create((int) $projectId, $_POST);
            flash('success', 'Evidencia registrada.');
            redirect('/investigaciones/' . $projectId . '?tab=evidencias');
        } catch (Throwable $e) {
            flash('error', $e->getMessage());
            redirect('/investigaciones/' . $projectId . '/evidencias/nueva');
        }
    }

    public static function edit(string $projectId, string $id): void
    {
        Auth::requireLogin();
        $project = ProjectService::requireOwned((int) $projectId, Auth::id());
        $item = EvidenceService::findInProject((int) $id, (int) $projectId);
        if (!$item) {
            flash('error', 'Evidencia no encontrada.');
            redirect('/investigaciones/' . $projectId . '?tab=evidencias');
        }
        view('evidence/form', [
            'title' => 'Editar evidencia',
            'project' => $project,
            'item' => $item,
            'claims' => ClaimService::forProject((int) $projectId),
            'sources' => SourceService::forProject((int) $projectId),
            'quotes' => QuoteService::forProject((int) $projectId),
        ]);
    }

    public static function update(string $projectId, string $id): void
    {
        Auth::requireLogin();
        require_csrf();
        ProjectService::requireOwned((int) $projectId, Auth::id());
        try {
            EvidenceService::update((int) $id, (int) $projectId, $_POST);
            flash('success', 'Evidencia actualizada.');
            redirect('/investigaciones/' . $projectId . '?tab=evidencias');
        } catch (Throwable $e) {
            flash('error', $e->getMessage());
            redirect('/investigaciones/' . $projectId . '/evidencias/' . $id . '/editar');
        }
    }

    public static function destroy(string $projectId, string $id): void
    {
        Auth::requireLogin();
        require_csrf();
        ProjectService::requireOwned((int) $projectId, Auth::id());
        EvidenceService::delete((int) $id, (int) $projectId);
        flash('success', 'Evidencia eliminada.');
        redirect('/investigaciones/' . $projectId . '?tab=evidencias');
    }
}
