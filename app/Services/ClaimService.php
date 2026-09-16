<?php

declare(strict_types=1);

final class ClaimService
{
    public static function forProject(int $projectId): array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT c.*, q.prompt AS question_prompt,
                (SELECT COUNT(*) FROM evidence_items e WHERE e.claim_id = c.id AND e.direction = \'supports\') AS support_count,
                (SELECT COUNT(*) FROM evidence_items e WHERE e.claim_id = c.id AND e.direction = \'against\') AS against_count
             FROM claims c
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
            'SELECT * FROM claims WHERE id = :id AND project_id = :pid LIMIT 1'
        );
        $stmt->execute(['id' => $id, 'pid' => $projectId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(int $projectId, array $data): int
    {
        $statement = trim((string) ($data['statement'] ?? ''));
        if ($statement === '') {
            throw new InvalidArgumentException('La afirmación es obligatoria.');
        }
        $confidence = max(0, min(100, (int) ($data['confidence'] ?? 50)));
        $stmt = Database::pdo()->prepare(
            'INSERT INTO claims (project_id, question_id, statement, claim_type, status, confidence, notes)
             VALUES (:pid, :question_id, :statement, :claim_type, :status, :confidence, :notes)'
        );
        $stmt->execute([
            'pid' => $projectId,
            'question_id' => int_or_null($data['question_id'] ?? null),
            'statement' => $statement,
            'claim_type' => $data['claim_type'] ?? 'factual',
            'status' => $data['status'] ?? 'hypothesis',
            'confidence' => $confidence,
            'notes' => null_if_blank($data['notes'] ?? null),
        ]);
        ProjectService::touch($projectId);
        return (int) Database::pdo()->lastInsertId();
    }

    public static function update(int $id, int $projectId, array $data): void
    {
        if (!self::findInProject($id, $projectId)) {
            throw new InvalidArgumentException('Afirmación no encontrada.');
        }
        $statement = trim((string) ($data['statement'] ?? ''));
        if ($statement === '') {
            throw new InvalidArgumentException('La afirmación es obligatoria.');
        }
        $confidence = max(0, min(100, (int) ($data['confidence'] ?? 50)));
        $stmt = Database::pdo()->prepare(
            'UPDATE claims SET question_id = :question_id, statement = :statement, claim_type = :claim_type,
             status = :status, confidence = :confidence, notes = :notes
             WHERE id = :id AND project_id = :pid'
        );
        $stmt->execute([
            'question_id' => int_or_null($data['question_id'] ?? null),
            'statement' => $statement,
            'claim_type' => $data['claim_type'] ?? 'factual',
            'status' => $data['status'] ?? 'hypothesis',
            'confidence' => $confidence,
            'notes' => null_if_blank($data['notes'] ?? null),
            'id' => $id,
            'pid' => $projectId,
        ]);
        ProjectService::touch($projectId);
    }

    public static function delete(int $id, int $projectId): void
    {
        $stmt = Database::pdo()->prepare('DELETE FROM claims WHERE id = :id AND project_id = :pid');
        $stmt->execute(['id' => $id, 'pid' => $projectId]);
        ProjectService::touch($projectId);
    }
}
