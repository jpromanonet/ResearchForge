<?php

declare(strict_types=1);

final class QuoteService
{
    public static function forProject(int $projectId): array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT q.*, s.title AS source_title, c.statement AS claim_statement
             FROM quotes q
             INNER JOIN sources s ON s.id = q.source_id
             LEFT JOIN claims c ON c.id = q.claim_id
             WHERE q.project_id = :pid
             ORDER BY q.updated_at DESC, q.id DESC'
        );
        $stmt->execute(['pid' => $projectId]);
        return $stmt->fetchAll();
    }

    public static function findInProject(int $id, int $projectId): ?array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT * FROM quotes WHERE id = :id AND project_id = :pid LIMIT 1'
        );
        $stmt->execute(['id' => $id, 'pid' => $projectId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(int $projectId, array $data): int
    {
        $excerpt = trim((string) ($data['excerpt'] ?? ''));
        $sourceId = (int) ($data['source_id'] ?? 0);
        if ($excerpt === '') {
            throw new InvalidArgumentException('La cita es obligatoria.');
        }
        if (!SourceService::findInProject($sourceId, $projectId)) {
            throw new InvalidArgumentException('Elegí una fuente válida.');
        }
        $claimId = int_or_null($data['claim_id'] ?? null);
        if ($claimId !== null && !ClaimService::findInProject($claimId, $projectId)) {
            throw new InvalidArgumentException('Afirmación inválida.');
        }
        $stmt = Database::pdo()->prepare(
            'INSERT INTO quotes (project_id, source_id, claim_id, excerpt, locator, context_note, translation)
             VALUES (:pid, :source_id, :claim_id, :excerpt, :locator, :context_note, :translation)'
        );
        $stmt->execute([
            'pid' => $projectId,
            'source_id' => $sourceId,
            'claim_id' => $claimId,
            'excerpt' => $excerpt,
            'locator' => null_if_blank($data['locator'] ?? null),
            'context_note' => null_if_blank($data['context_note'] ?? null),
            'translation' => null_if_blank($data['translation'] ?? null),
        ]);
        ProjectService::touch($projectId);
        return (int) Database::pdo()->lastInsertId();
    }

    public static function update(int $id, int $projectId, array $data): void
    {
        if (!self::findInProject($id, $projectId)) {
            throw new InvalidArgumentException('Cita no encontrada.');
        }
        $excerpt = trim((string) ($data['excerpt'] ?? ''));
        $sourceId = (int) ($data['source_id'] ?? 0);
        if ($excerpt === '') {
            throw new InvalidArgumentException('La cita es obligatoria.');
        }
        if (!SourceService::findInProject($sourceId, $projectId)) {
            throw new InvalidArgumentException('Elegí una fuente válida.');
        }
        $claimId = int_or_null($data['claim_id'] ?? null);
        if ($claimId !== null && !ClaimService::findInProject($claimId, $projectId)) {
            throw new InvalidArgumentException('Afirmación inválida.');
        }
        $stmt = Database::pdo()->prepare(
            'UPDATE quotes SET source_id = :source_id, claim_id = :claim_id, excerpt = :excerpt,
             locator = :locator, context_note = :context_note, translation = :translation
             WHERE id = :id AND project_id = :pid'
        );
        $stmt->execute([
            'source_id' => $sourceId,
            'claim_id' => $claimId,
            'excerpt' => $excerpt,
            'locator' => null_if_blank($data['locator'] ?? null),
            'context_note' => null_if_blank($data['context_note'] ?? null),
            'translation' => null_if_blank($data['translation'] ?? null),
            'id' => $id,
            'pid' => $projectId,
        ]);
        ProjectService::touch($projectId);
    }

    public static function delete(int $id, int $projectId): void
    {
        $stmt = Database::pdo()->prepare('DELETE FROM quotes WHERE id = :id AND project_id = :pid');
        $stmt->execute(['id' => $id, 'pid' => $projectId]);
        ProjectService::touch($projectId);
    }
}
