<?php

declare(strict_types=1);

final class ProjectService
{
    public static function forUser(int $userId, ?string $status = null): array
    {
        $sql = 'SELECT p.*,
            (SELECT COUNT(*) FROM questions q WHERE q.project_id = p.id) AS questions_count,
            (SELECT COUNT(*) FROM sources s WHERE s.project_id = p.id) AS sources_count,
            (SELECT COUNT(*) FROM claims c WHERE c.project_id = p.id) AS claims_count,
            (SELECT COUNT(*) FROM dossiers d WHERE d.project_id = p.id) AS dossiers_count
            FROM projects p WHERE p.user_id = :uid';
        $params = ['uid' => $userId];
        if ($status !== null && $status !== '') {
            $sql .= ' AND p.status = :status';
            $params['status'] = $status;
        }
        $sql .= ' ORDER BY p.updated_at DESC';
        $stmt = Database::pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function findOwned(int $id, int $userId): ?array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT * FROM projects WHERE id = :id AND user_id = :uid LIMIT 1'
        );
        $stmt->execute(['id' => $id, 'uid' => $userId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function requireOwned(int $id, int $userId): array
    {
        $project = self::findOwned($id, $userId);
        if (!$project) {
            flash('error', 'Investigación no encontrada.');
            redirect('/investigaciones');
        }
        return $project;
    }

    public static function create(int $userId, array $data): int
    {
        $title = trim((string) ($data['title'] ?? ''));
        if ($title === '') {
            throw new InvalidArgumentException('El título es obligatorio.');
        }
        $stmt = Database::pdo()->prepare(
            'INSERT INTO projects (user_id, title, summary, status, discipline, confidentiality)
             VALUES (:uid, :title, :summary, :status, :discipline, :confidentiality)'
        );
        $stmt->execute([
            'uid' => $userId,
            'title' => $title,
            'summary' => null_if_blank($data['summary'] ?? null),
            'status' => $data['status'] ?? 'active',
            'discipline' => null_if_blank($data['discipline'] ?? null),
            'confidentiality' => $data['confidentiality'] ?? 'private',
        ]);
        return (int) Database::pdo()->lastInsertId();
    }

    public static function update(int $id, int $userId, array $data): void
    {
        self::requireOwned($id, $userId);
        $title = trim((string) ($data['title'] ?? ''));
        if ($title === '') {
            throw new InvalidArgumentException('El título es obligatorio.');
        }
        $stmt = Database::pdo()->prepare(
            'UPDATE projects SET title = :title, summary = :summary, status = :status,
             discipline = :discipline, confidentiality = :confidentiality
             WHERE id = :id AND user_id = :uid'
        );
        $stmt->execute([
            'title' => $title,
            'summary' => null_if_blank($data['summary'] ?? null),
            'status' => $data['status'] ?? 'active',
            'discipline' => null_if_blank($data['discipline'] ?? null),
            'confidentiality' => $data['confidentiality'] ?? 'private',
            'id' => $id,
            'uid' => $userId,
        ]);
    }

    public static function delete(int $id, int $userId): void
    {
        $stmt = Database::pdo()->prepare('DELETE FROM projects WHERE id = :id AND user_id = :uid');
        $stmt->execute(['id' => $id, 'uid' => $userId]);
    }

    public static function touch(int $projectId): void
    {
        $stmt = Database::pdo()->prepare('UPDATE projects SET updated_at = NOW() WHERE id = :id');
        $stmt->execute(['id' => $projectId]);
    }

    public static function bundle(int $projectId): array
    {
        return [
            'questions' => QuestionService::forProject($projectId),
            'sources' => SourceService::forProject($projectId),
            'claims' => ClaimService::forProject($projectId),
            'quotes' => QuoteService::forProject($projectId),
            'evidence' => EvidenceService::forProject($projectId),
            'conclusions' => ConclusionService::forProject($projectId),
            'dossiers' => DossierService::forProject($projectId),
        ];
    }
}
