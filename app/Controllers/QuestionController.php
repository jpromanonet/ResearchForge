<?php

declare(strict_types=1);

final class QuestionController
{
    public static function create(string $projectId): void
    {
        Auth::requireLogin();
        $project = ProjectService::requireOwned((int) $projectId, Auth::id());
        view('questions/form', [
            'title' => 'Nueva pregunta',
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
            QuestionService::create((int) $projectId, $_POST);
            flash('success', 'Pregunta agregada.');
            redirect('/investigaciones/' . $projectId . '?tab=preguntas');
        } catch (Throwable $e) {
            flash('error', $e->getMessage());
            redirect('/investigaciones/' . $projectId . '/preguntas/nueva');
        }
    }

    public static function edit(string $projectId, string $id): void
    {
        Auth::requireLogin();
        $project = ProjectService::requireOwned((int) $projectId, Auth::id());
        $item = QuestionService::findInProject((int) $id, (int) $projectId);
        if (!$item) {
            flash('error', 'Pregunta no encontrada.');
            redirect('/investigaciones/' . $projectId . '?tab=preguntas');
        }
        view('questions/form', [
            'title' => 'Editar pregunta',
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
            QuestionService::update((int) $id, (int) $projectId, $_POST);
            flash('success', 'Pregunta actualizada.');
            redirect('/investigaciones/' . $projectId . '?tab=preguntas');
        } catch (Throwable $e) {
            flash('error', $e->getMessage());
            redirect('/investigaciones/' . $projectId . '/preguntas/' . $id . '/editar');
        }
    }

    public static function destroy(string $projectId, string $id): void
    {
        Auth::requireLogin();
        require_csrf();
        ProjectService::requireOwned((int) $projectId, Auth::id());
        QuestionService::delete((int) $id, (int) $projectId);
        flash('success', 'Pregunta eliminada.');
        redirect('/investigaciones/' . $projectId . '?tab=preguntas');
    }
}
