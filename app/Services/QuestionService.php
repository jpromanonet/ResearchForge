<?php

declare(strict_types=1);

final class QuestionService
{
    public static function forProject(int $projectId): array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT * FROM questions WHERE project_id = :pid ORDER BY sort_order ASC, id ASC'
        );
        $stmt->execute(['pid' => $projectId]);
        return $stmt->fetchAll();
    }

    public static function findInProject(int $id, int $projectId): ?array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT * FROM questions WHERE id = :id AND project_id = :pid LIMIT 1'
        );
        $stmt->execute(['id' => $id, 'pid' => $projectId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(int $projectId, array $data): int
    {
        $prompt = trim((string) ($data['prompt'] ?? ''));
        if ($prompt === '') {
            throw new InvalidArgumentException('La pregunta es obligatoria.');
        }
        $stmt = Database::pdo()->prepare(
            'INSERT INTO questions (project_id, parent_id, prompt, kind, priority, status, notes, sort_order)
             VALUES (:pid, :parent_id, :prompt, :kind, :priority, :status, :notes, :sort_order)'
        );
        $stmt->execute([
            'pid' => $projectId,
            'parent_id' => int_or_null($data['parent_id'] ?? null),
            'prompt' => $prompt,
            'kind' => $data['kind'] ?? 'open',
            'priority' => $data['priority'] ?? 'medium',
            'status' => $data['status'] ?? 'open',
            'notes' => null_if_blank($data['notes'] ?? null),
            'sort_order' => (int) ($data['sort_order'] ?? 0),
        ]);
        ProjectService::touch($projectId);
        return (int) Database::pdo()->lastInsertId();
    }

    public static function update(int $id, int $projectId, array $data): void
    {
        if (!self::findInProject($id, $projectId)) {
            throw new InvalidArgumentException('Pregunta no encontrada.');
        }
        $prompt = trim((string) ($data['prompt'] ?? ''));
        if ($prompt === '') {
            throw new InvalidArgumentException('La pregunta es obligatoria.');
        }
        $stmt = Database::pdo()->prepare(
            'UPDATE questions SET parent_id = :parent_id, prompt = :prompt, kind = :kind,
             priority = :priority, status = :status, notes = :notes, sort_order = :sort_order
             WHERE id = :id AND project_id = :pid'
        );
        $stmt->execute([
            'parent_id' => int_or_null($data['parent_id'] ?? null),
            'prompt' => $prompt,
            'kind' => $data['kind'] ?? 'open',
            'priority' => $data['priority'] ?? 'medium',
            'status' => $data['status'] ?? 'open',
            'notes' => null_if_blank($data['notes'] ?? null),
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'id' => $id,
            'pid' => $projectId,
        ]);
        ProjectService::touch($projectId);
    }

    public static function delete(int $id, int $projectId): void
    {
        $stmt = Database::pdo()->prepare('DELETE FROM questions WHERE id = :id AND project_id = :pid');
        $stmt->execute(['id' => $id, 'pid' => $projectId]);
        ProjectService::touch($projectId);
    }
}
