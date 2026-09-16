<?php

declare(strict_types=1);

final class EvidenceService
{
    public static function forProject(int $projectId): array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT e.*, c.statement AS claim_statement, s.title AS source_title,
                    LEFT(q.excerpt, 160) AS quote_excerpt
             FROM evidence_items e
             INNER JOIN claims c ON c.id = e.claim_id
             LEFT JOIN sources s ON s.id = e.source_id
             LEFT JOIN quotes q ON q.id = e.quote_id
             WHERE e.project_id = :pid
             ORDER BY e.updated_at DESC, e.id DESC'
        );
        $stmt->execute(['pid' => $projectId]);
        return $stmt->fetchAll();
    }

    public static function findInProject(int $id, int $projectId): ?array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT * FROM evidence_items WHERE id = :id AND project_id = :pid LIMIT 1'
        );
        $stmt->execute(['id' => $id, 'pid' => $projectId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(int $projectId, array $data): int
    {
        $summary = trim((string) ($data['summary'] ?? ''));
        $claimId = (int) ($data['claim_id'] ?? 0);
        if ($summary === '') {
            throw new InvalidArgumentException('El resumen de la evidencia es obligatorio.');
        }
        if (!ClaimService::findInProject($claimId, $projectId)) {
            throw new InvalidArgumentException('Elegí una afirmación válida.');
        }
        $sourceId = int_or_null($data['source_id'] ?? null);
        if ($sourceId !== null && !SourceService::findInProject($sourceId, $projectId)) {
            throw new InvalidArgumentException('Fuente inválida.');
        }
        $quoteId = int_or_null($data['quote_id'] ?? null);
        if ($quoteId !== null && !QuoteService::findInProject($quoteId, $projectId)) {
            throw new InvalidArgumentException('Cita inválida.');
        }
        $stmt = Database::pdo()->prepare(
            'INSERT INTO evidence_items (project_id, claim_id, quote_id, source_id, direction, strength, evidence_type, summary, notes)
             VALUES (:pid, :claim_id, :quote_id, :source_id, :direction, :strength, :evidence_type, :summary, :notes)'
        );
        $stmt->execute([
            'pid' => $projectId,
            'claim_id' => $claimId,
            'quote_id' => $quoteId,
            'source_id' => $sourceId,
            'direction' => $data['direction'] ?? 'supports',
            'strength' => $data['strength'] ?? 'moderate',
            'evidence_type' => $data['evidence_type'] ?? 'document',
            'summary' => $summary,
            'notes' => null_if_blank($data['notes'] ?? null),
        ]);
        ProjectService::touch($projectId);
        return (int) Database::pdo()->lastInsertId();
    }

    public static function update(int $id, int $projectId, array $data): void
    {
        if (!self::findInProject($id, $projectId)) {
            throw new InvalidArgumentException('Evidencia no encontrada.');
        }
        $summary = trim((string) ($data['summary'] ?? ''));
        $claimId = (int) ($data['claim_id'] ?? 0);
        if ($summary === '') {
            throw new InvalidArgumentException('El resumen de la evidencia es obligatorio.');
        }
        if (!ClaimService::findInProject($claimId, $projectId)) {
            throw new InvalidArgumentException('Elegí una afirmación válida.');
        }
        $sourceId = int_or_null($data['source_id'] ?? null);
        if ($sourceId !== null && !SourceService::findInProject($sourceId, $projectId)) {
            throw new InvalidArgumentException('Fuente inválida.');
        }
        $quoteId = int_or_null($data['quote_id'] ?? null);
        if ($quoteId !== null && !QuoteService::findInProject($quoteId, $projectId)) {
            throw new InvalidArgumentException('Cita inválida.');
        }
        $stmt = Database::pdo()->prepare(
            'UPDATE evidence_items SET claim_id = :claim_id, quote_id = :quote_id, source_id = :source_id,
             direction = :direction, strength = :strength, evidence_type = :evidence_type,
             summary = :summary, notes = :notes
             WHERE id = :id AND project_id = :pid'
        );
        $stmt->execute([
            'claim_id' => $claimId,
            'quote_id' => $quoteId,
            'source_id' => $sourceId,
            'direction' => $data['direction'] ?? 'supports',
            'strength' => $data['strength'] ?? 'moderate',
            'evidence_type' => $data['evidence_type'] ?? 'document',
            'summary' => $summary,
            'notes' => null_if_blank($data['notes'] ?? null),
            'id' => $id,
            'pid' => $projectId,
        ]);
        ProjectService::touch($projectId);
    }

    public static function delete(int $id, int $projectId): void
    {
        $stmt = Database::pdo()->prepare('DELETE FROM evidence_items WHERE id = :id AND project_id = :pid');
        $stmt->execute(['id' => $id, 'pid' => $projectId]);
        ProjectService::touch($projectId);
    }
}
