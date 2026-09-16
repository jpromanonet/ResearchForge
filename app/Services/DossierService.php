<?php

declare(strict_types=1);

final class DossierService
{
    public static function forProject(int $projectId): array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT d.*,
                (SELECT COUNT(*) FROM dossier_items di WHERE di.dossier_id = d.id) AS items_count
             FROM dossiers d
             WHERE d.project_id = :pid
             ORDER BY d.updated_at DESC, d.id DESC'
        );
        $stmt->execute(['pid' => $projectId]);
        return $stmt->fetchAll();
    }

    public static function findInProject(int $id, int $projectId): ?array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT * FROM dossiers WHERE id = :id AND project_id = :pid LIMIT 1'
        );
        $stmt->execute(['id' => $id, 'pid' => $projectId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function items(int $dossierId): array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT * FROM dossier_items WHERE dossier_id = :did ORDER BY sort_order ASC, id ASC'
        );
        $stmt->execute(['did' => $dossierId]);
        return $stmt->fetchAll();
    }

    public static function create(int $projectId, array $data): int
    {
        $title = trim((string) ($data['title'] ?? ''));
        if ($title === '') {
            throw new InvalidArgumentException('El título del dossier es obligatorio.');
        }
        $stmt = Database::pdo()->prepare(
            'INSERT INTO dossiers (project_id, title, subtitle, mode, status, executive_summary)
             VALUES (:pid, :title, :subtitle, :mode, :status, :executive_summary)'
        );
        $stmt->execute([
            'pid' => $projectId,
            'title' => $title,
            'subtitle' => null_if_blank($data['subtitle'] ?? null),
            'mode' => $data['mode'] ?? 'briefing',
            'status' => $data['status'] ?? 'draft',
            'executive_summary' => null_if_blank($data['executive_summary'] ?? null),
        ]);
        $id = (int) Database::pdo()->lastInsertId();
        self::syncItems($id, $projectId, $data['include'] ?? []);
        ProjectService::touch($projectId);
        return $id;
    }

    public static function update(int $id, int $projectId, array $data): void
    {
        if (!self::findInProject($id, $projectId)) {
            throw new InvalidArgumentException('Dossier no encontrado.');
        }
        $title = trim((string) ($data['title'] ?? ''));
        if ($title === '') {
            throw new InvalidArgumentException('El título del dossier es obligatorio.');
        }
        $stmt = Database::pdo()->prepare(
            'UPDATE dossiers SET title = :title, subtitle = :subtitle, mode = :mode,
             status = :status, executive_summary = :executive_summary
             WHERE id = :id AND project_id = :pid'
        );
        $stmt->execute([
            'title' => $title,
            'subtitle' => null_if_blank($data['subtitle'] ?? null),
            'mode' => $data['mode'] ?? 'briefing',
            'status' => $data['status'] ?? 'draft',
            'executive_summary' => null_if_blank($data['executive_summary'] ?? null),
            'id' => $id,
            'pid' => $projectId,
        ]);
        self::syncItems($id, $projectId, $data['include'] ?? []);
        ProjectService::touch($projectId);
    }

    public static function delete(int $id, int $projectId): void
    {
        $stmt = Database::pdo()->prepare('DELETE FROM dossiers WHERE id = :id AND project_id = :pid');
        $stmt->execute(['id' => $id, 'pid' => $projectId]);
        ProjectService::touch($projectId);
    }

    /** @param array<string, list<int|string>> $include */
    public static function syncItems(int $dossierId, int $projectId, array $include): void
    {
        Database::pdo()->prepare('DELETE FROM dossier_items WHERE dossier_id = :did')
            ->execute(['did' => $dossierId]);

        $map = [
            'questions' => ['section' => 'questions', 'type' => 'question', 'table' => 'questions'],
            'sources' => ['section' => 'sources', 'type' => 'source', 'table' => 'sources'],
            'claims' => ['section' => 'claims', 'type' => 'claim', 'table' => 'claims'],
            'quotes' => ['section' => 'quotes', 'type' => 'quote', 'table' => 'quotes'],
            'evidence' => ['section' => 'evidence', 'type' => 'evidence', 'table' => 'evidence_items'],
            'conclusions' => ['section' => 'conclusions', 'type' => 'conclusion', 'table' => 'conclusions'],
        ];

        $insert = Database::pdo()->prepare(
            'INSERT INTO dossier_items (dossier_id, section, item_type, item_id, sort_order)
             VALUES (:did, :section, :item_type, :item_id, :sort_order)'
        );

        $order = 0;
        foreach ($map as $key => $meta) {
            $ids = $include[$key] ?? [];
            if (!is_array($ids)) {
                continue;
            }
            foreach ($ids as $rawId) {
                $itemId = (int) $rawId;
                if ($itemId <= 0) {
                    continue;
                }
                $check = Database::pdo()->prepare(
                    "SELECT id FROM {$meta['table']} WHERE id = :id AND project_id = :pid LIMIT 1"
                );
                $check->execute(['id' => $itemId, 'pid' => $projectId]);
                if (!$check->fetch()) {
                    continue;
                }
                $insert->execute([
                    'did' => $dossierId,
                    'section' => $meta['section'],
                    'item_type' => $meta['type'],
                    'item_id' => $itemId,
                    'sort_order' => $order++,
                ]);
            }
        }
    }

    public static function assemble(int $dossierId, int $projectId): ?array
    {
        $dossier = self::findInProject($dossierId, $projectId);
        if (!$dossier) {
            return null;
        }
        $project = Database::pdo()->prepare('SELECT * FROM projects WHERE id = :id LIMIT 1');
        $project->execute(['id' => $projectId]);
        $projectRow = $project->fetch() ?: [];

        $items = self::items($dossierId);
        $sections = [
            'questions' => [],
            'sources' => [],
            'claims' => [],
            'quotes' => [],
            'evidence' => [],
            'conclusions' => [],
        ];

        foreach ($items as $item) {
            $row = match ($item['item_type']) {
                'question' => QuestionService::findInProject((int) $item['item_id'], $projectId),
                'source' => SourceService::findInProject((int) $item['item_id'], $projectId),
                'claim' => ClaimService::findInProject((int) $item['item_id'], $projectId),
                'quote' => QuoteService::findInProject((int) $item['item_id'], $projectId),
                'evidence' => EvidenceService::findInProject((int) $item['item_id'], $projectId),
                'conclusion' => ConclusionService::findInProject((int) $item['item_id'], $projectId),
                default => null,
            };
            if ($row) {
                $sections[$item['section']][] = $row;
            }
        }

        // Enrich quotes with source titles for export
        foreach ($sections['quotes'] as &$quote) {
            $src = SourceService::findInProject((int) $quote['source_id'], $projectId);
            $quote['source_title'] = $src['title'] ?? '';
        }
        unset($quote);

        foreach ($sections['evidence'] as &$ev) {
            $claim = ClaimService::findInProject((int) $ev['claim_id'], $projectId);
            $ev['claim_statement'] = $claim['statement'] ?? '';
        }
        unset($ev);

        return [
            'dossier' => $dossier,
            'project' => $projectRow,
            'sections' => $sections,
            'selected' => self::selectedMap($items),
        ];
    }

    public static function selectedMap(array $items): array
    {
        $out = [
            'questions' => [],
            'sources' => [],
            'claims' => [],
            'quotes' => [],
            'evidence' => [],
            'conclusions' => [],
        ];
        $typeKey = [
            'question' => 'questions',
            'source' => 'sources',
            'claim' => 'claims',
            'quote' => 'quotes',
            'evidence' => 'evidence',
            'conclusion' => 'conclusions',
        ];
        foreach ($items as $item) {
            $key = $typeKey[$item['item_type']] ?? null;
            if ($key) {
                $out[$key][] = (int) $item['item_id'];
            }
        }
        return $out;
    }

    public static function toMarkdown(array $assembled): string
    {
        $d = $assembled['dossier'];
        $p = $assembled['project'];
        $s = $assembled['sections'];
        $lines = [];
        $lines[] = '# ' . $d['title'];
        if (!empty($d['subtitle'])) {
            $lines[] = '_' . $d['subtitle'] . '_';
        }
        $lines[] = '';
        $lines[] = '**Investigación:** ' . ($p['title'] ?? '');
        $lines[] = '**Modo:** ' . (dossier_modes()[$d['mode']] ?? $d['mode']);
        $lines[] = '**Estado:** ' . (dossier_statuses()[$d['status']] ?? $d['status']);
        $lines[] = '**Generado:** ' . date('Y-m-d H:i');
        $lines[] = '';
        if (!empty($d['executive_summary'])) {
            $lines[] = '## Resumen ejecutivo';
            $lines[] = $d['executive_summary'];
            $lines[] = '';
        }
        if ($s['questions']) {
            $lines[] = '## Preguntas';
            foreach ($s['questions'] as $q) {
                $lines[] = '- ' . $q['prompt'] . ' _(' . (question_statuses()[$q['status']] ?? $q['status']) . ')_';
            }
            $lines[] = '';
        }
        if ($s['sources']) {
            $lines[] = '## Fuentes';
            foreach ($s['sources'] as $src) {
                $meta = trim(($src['authors'] ?? '') . ($src['year'] ? ' (' . $src['year'] . ')' : ''));
                $lines[] = '- **' . $src['title'] . '**' . ($meta !== '' ? ' — ' . $meta : '');
                if (!empty($src['url'])) {
                    $lines[] = '  - ' . $src['url'];
                }
            }
            $lines[] = '';
        }
        if ($s['claims']) {
            $lines[] = '## Afirmaciones';
            foreach ($s['claims'] as $c) {
                $lines[] = '- ' . $c['statement'] . ' _(' . (claim_statuses()[$c['status']] ?? $c['status']) . ', confianza ' . $c['confidence'] . '%)_';
            }
            $lines[] = '';
        }
        if ($s['quotes']) {
            $lines[] = '## Citas';
            foreach ($s['quotes'] as $q) {
                $lines[] = '> ' . str_replace("\n", "\n> ", $q['excerpt']);
                $loc = $q['locator'] ? ', ' . $q['locator'] : '';
                $lines[] = '';
                $lines[] = '— *' . ($q['source_title'] ?? 'Fuente') . $loc . '*';
                $lines[] = '';
            }
        }
        if ($s['evidence']) {
            $lines[] = '## Evidencias';
            foreach ($s['evidence'] as $ev) {
                $dir = evidence_directions()[$ev['direction']] ?? $ev['direction'];
                $str = evidence_strengths()[$ev['strength']] ?? $ev['strength'];
                $lines[] = '- **[' . $dir . ' · ' . $str . ']** ' . $ev['summary'];
                if (!empty($ev['claim_statement'])) {
                    $lines[] = '  - Claim: ' . $ev['claim_statement'];
                }
            }
            $lines[] = '';
        }
        if ($s['conclusions']) {
            $lines[] = '## Conclusiones';
            foreach ($s['conclusions'] as $c) {
                $lines[] = '### ' . $c['title'];
                $lines[] = $c['body'];
                if (!empty($c['limitations'])) {
                    $lines[] = '';
                    $lines[] = '**Limitaciones:** ' . $c['limitations'];
                }
                if (!empty($c['next_steps'])) {
                    $lines[] = '';
                    $lines[] = '**Próximos pasos:** ' . $c['next_steps'];
                }
                $lines[] = '';
            }
        }
        $lines[] = '---';
        $lines[] = '_Exportado desde ResearchForge_';
        return implode("\n", $lines);
    }

    public static function toHtml(array $assembled): string
    {
        $md = self::toMarkdown($assembled);
        $escaped = htmlspecialchars($md, ENT_QUOTES, 'UTF-8');
        $d = $assembled['dossier'];
        $body = nl2br($escaped);
        // Simple structure from sections for nicer HTML
        ob_start();
        ?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title><?= htmlspecialchars($d['title'], ENT_QUOTES, 'UTF-8') ?></title>
<style>
body{font-family:Georgia,serif;max-width:42rem;margin:2rem auto;padding:0 1rem;color:#1A2332;line-height:1.55;background:#F3EFE6}
h1,h2,h3{font-family:Georgia,serif}
.meta{color:#5a6570;font-size:.9rem}
blockquote{border-left:3px solid #B86B3A;margin:1rem 0;padding:.25rem 1rem;color:#2a3544}
.badge{display:inline-block;border:1px solid #1A2332;padding:.1rem .4rem;font-size:.75rem;margin-right:.35rem}
hr{border:none;border-top:1px solid #c9c2b5;margin:2rem 0}
@media print{body{background:#fff;margin:0}}
</style>
</head>
<body>
<pre style="white-space:pre-wrap;font-family:Georgia,serif"><?= $escaped ?></pre>
</body>
</html>
        <?php
        return (string) ob_get_clean();
    }

    public static function toJson(array $assembled): string
    {
        return (string) json_encode($assembled, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}
