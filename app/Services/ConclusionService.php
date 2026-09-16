<?php

declare(strict_types=1);

final class ConclusionService
{
    public static function forProject(int $projectId): array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT c.*, q.prompt AS question_prompt
             FROM conclusions c
             LEFT JOIN questions q ON q.id = c.question_id
             WHERE c.project_id = :pid
             ORDER BY c.updated_at DESC, c.id DESC'
        );
        $stmt->execute(['pid' => $projectId]);
        return $stmt->fetchAll();
    }

    public static function findInProject(int $id, int $projectId): ?array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT * FROM conclusions WHERE id = :id AND project_id = :pid LIMIT 1'
        );
        $stmt->execute(['id' => $id, 'pid' => $projectId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(int $projectId, array $data): int
    {
        $title = trim((string) ($data['title'] ?? ''));
        $body = trim((string) ($data['body'] ?? ''));
        if ($title === '' || $body === '') {
            throw new InvalidArgumentException('Título y desarrollo son obligatorios.');
        }
        $questionId = int_or_null($data['question_id'] ?? null);
        if ($questionId !== null && !QuestionService::findInProject($questionId, $projectId)) {
            throw new InvalidArgumentException('Pregunta inválida.');
        }
        $stmt = Database::pdo()->prepare(
            'INSERT INTO conclusions (project_id, question_id, title, body, conclusion_type, limitations, next_steps)
             VALUES (:pid, :question_id, :title, :body, :conclusion_type, :limitations, :next_steps)'
        );
        $stmt->execute([
            'pid' => $projectId,
            'question_id' => $questionId,
            'title' => $title,
            'body' => $body,
            'conclusion_type' => $data['conclusion_type'] ?? 'provisional',
            'limitations' => null_if_blank($data['limitations'] ?? null),
            'next_steps' => null_if_blank($data['next_steps'] ?? null),
        ]);
        ProjectService::touch($projectId);
        return (int) Database::pdo()->lastInsertId();
    }

    public static function update(int $id, int $projectId, array $data): void
    {
        if (!self::findInProject($id, $projectId)) {
            throw new InvalidArgumentException('Conclusión no encontrada.');
        }
        $title = trim((string) ($data['title'] ?? ''));
        $body = trim((string) ($data['body'] ?? ''));
        if ($title === '' || $body === '') {
            throw new InvalidArgumentException('Título y desarrollo son obligatorios.');
        }
        $questionId = int_or_null($data['question_id'] ?? null);
        if ($questionId !== null && !QuestionService::findInProject($questionId, $projectId)) {
            throw new InvalidArgumentException('Pregunta inválida.');
        }
        $stmt = Database::pdo()->prepare(
            'UPDATE conclusions SET question_id = :question_id, title = :title, body = :body,
             conclusion_type = :conclusion_type, limitations = :limitations, next_steps = :next_steps
             WHERE id = :id AND project_id = :pid'
        );
        $stmt->execute([
            'question_id' => $questionId,
            'title' => $title,
            'body' => $body,
            'conclusion_type' => $data['conclusion_type'] ?? 'provisional',
            'limitations' => null_if_blank($data['limitations'] ?? null),
            'next_steps' => null_if_blank($data['next_steps'] ?? null),
            'id' => $id,
            'pid' => $projectId,
        ]);
        ProjectService::touch($projectId);
    }

    public static function delete(int $id, int $projectId): void
    {
        $stmt = Database::pdo()->prepare('DELETE FROM conclusions WHERE id = :id AND project_id = :pid');
        $stmt->execute(['id' => $id, 'pid' => $projectId]);
        ProjectService::touch($projectId);
    }
}
