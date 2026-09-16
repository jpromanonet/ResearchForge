<?php

declare(strict_types=1);

final class SourceService
{
    public static function forProject(int $projectId): array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT * FROM sources WHERE project_id = :pid ORDER BY updated_at DESC, id DESC'
        );
        $stmt->execute(['pid' => $projectId]);
        return $stmt->fetchAll();
    }

    public static function findInProject(int $id, int $projectId): ?array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT * FROM sources WHERE id = :id AND project_id = :pid LIMIT 1'
        );
        $stmt->execute(['id' => $id, 'pid' => $projectId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(int $projectId, array $data): int
    {
        $title = trim((string) ($data['title'] ?? ''));
        if ($title === '') {
            throw new InvalidArgumentException('El título de la fuente es obligatorio.');
        }
        $stmt = Database::pdo()->prepare(
            'INSERT INTO sources (project_id, title, source_type, authors, year, publisher, url, doi, isbn,
             accessed_at, language, reliability, bias_notes, reading_status, notes)
             VALUES (:pid, :title, :source_type, :authors, :year, :publisher, :url, :doi, :isbn,
             :accessed_at, :language, :reliability, :bias_notes, :reading_status, :notes)'
        );
        $stmt->execute(self::bind($projectId, $data, $title));
        ProjectService::touch($projectId);
        return (int) Database::pdo()->lastInsertId();
    }

    public static function update(int $id, int $projectId, array $data): void
    {
        if (!self::findInProject($id, $projectId)) {
            throw new InvalidArgumentException('Fuente no encontrada.');
        }
        $title = trim((string) ($data['title'] ?? ''));
        if ($title === '') {
            throw new InvalidArgumentException('El título de la fuente es obligatorio.');
        }
        $bind = self::bind($projectId, $data, $title);
        $bind['id'] = $id;
        $stmt = Database::pdo()->prepare(
            'UPDATE sources SET title = :title, source_type = :source_type, authors = :authors, year = :year,
             publisher = :publisher, url = :url, doi = :doi, isbn = :isbn, accessed_at = :accessed_at,
             language = :language, reliability = :reliability, bias_notes = :bias_notes,
             reading_status = :reading_status, notes = :notes
             WHERE id = :id AND project_id = :pid'
        );
        $stmt->execute($bind);
        ProjectService::touch($projectId);
    }

    public static function delete(int $id, int $projectId): void
    {
        $stmt = Database::pdo()->prepare('DELETE FROM sources WHERE id = :id AND project_id = :pid');
        $stmt->execute(['id' => $id, 'pid' => $projectId]);
        ProjectService::touch($projectId);
    }

    private static function bind(int $projectId, array $data, string $title): array
    {
        $rel = int_or_null($data['reliability'] ?? null);
        if ($rel !== null) {
            $rel = max(1, min(5, $rel));
        }
        return [
            'pid' => $projectId,
            'title' => $title,
            'source_type' => $data['source_type'] ?? 'article',
            'authors' => null_if_blank($data['authors'] ?? null),
            'year' => int_or_null($data['year'] ?? null),
            'publisher' => null_if_blank($data['publisher'] ?? null),
            'url' => null_if_blank($data['url'] ?? null),
            'doi' => null_if_blank($data['doi'] ?? null),
            'isbn' => null_if_blank($data['isbn'] ?? null),
            'accessed_at' => null_if_blank($data['accessed_at'] ?? null),
            'language' => null_if_blank($data['language'] ?? null),
            'reliability' => $rel,
            'bias_notes' => null_if_blank($data['bias_notes'] ?? null),
            'reading_status' => $data['reading_status'] ?? 'to_read',
            'notes' => null_if_blank($data['notes'] ?? null),
        ];
    }
}
