<?php

declare(strict_types=1);

final class StatsService
{
    public static function forUser(int $userId): array
    {
        $pdo = Database::pdo();
        $count = static function (string $sql, array $params) use ($pdo): int {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return (int) $stmt->fetchColumn();
        };

        $recent = $pdo->prepare(
            'SELECT id, title, status, updated_at FROM projects
             WHERE user_id = :uid ORDER BY updated_at DESC LIMIT 6'
        );
        $recent->execute(['uid' => $userId]);

        $openQuestions = $pdo->prepare(
            'SELECT q.id, q.prompt, q.status, p.title AS project_title, p.id AS project_id
             FROM questions q
             INNER JOIN projects p ON p.id = q.project_id
             WHERE p.user_id = :uid AND q.status IN (\'open\', \'in_progress\')
             ORDER BY q.updated_at DESC LIMIT 8'
        );
        $openQuestions->execute(['uid' => $userId]);

        $disputed = $pdo->prepare(
            'SELECT c.id, c.statement, c.status, p.title AS project_title, p.id AS project_id
             FROM claims c
             INNER JOIN projects p ON p.id = c.project_id
             WHERE p.user_id = :uid AND c.status IN (\'disputed\', \'hypothesis\')
             ORDER BY c.updated_at DESC LIMIT 8'
        );
        $disputed->execute(['uid' => $userId]);

        return [
            'projects' => $count('SELECT COUNT(*) FROM projects WHERE user_id = :uid', ['uid' => $userId]),
            'questions' => $count(
                'SELECT COUNT(*) FROM questions q INNER JOIN projects p ON p.id = q.project_id WHERE p.user_id = :uid',
                ['uid' => $userId]
            ),
            'sources' => $count(
                'SELECT COUNT(*) FROM sources s INNER JOIN projects p ON p.id = s.project_id WHERE p.user_id = :uid',
                ['uid' => $userId]
            ),
            'claims' => $count(
                'SELECT COUNT(*) FROM claims c INNER JOIN projects p ON p.id = c.project_id WHERE p.user_id = :uid',
                ['uid' => $userId]
            ),
            'evidence' => $count(
                'SELECT COUNT(*) FROM evidence_items e INNER JOIN projects p ON p.id = e.project_id WHERE p.user_id = :uid',
                ['uid' => $userId]
            ),
            'dossiers' => $count(
                'SELECT COUNT(*) FROM dossiers d INNER JOIN projects p ON p.id = d.project_id WHERE p.user_id = :uid',
                ['uid' => $userId]
            ),
            'recent_projects' => $recent->fetchAll(),
            'open_questions' => $openQuestions->fetchAll(),
            'watch_claims' => $disputed->fetchAll(),
        ];
    }

    public static function search(int $userId, string $q): array
    {
        $q = trim($q);
        if ($q === '') {
            return ['projects' => [], 'questions' => [], 'sources' => [], 'claims' => [], 'quotes' => []];
        }
        $like = '%' . $q . '%';
        $pdo = Database::pdo();

        $projects = $pdo->prepare(
            'SELECT id, title, status FROM projects WHERE user_id = :uid AND (title LIKE :q OR summary LIKE :q2) LIMIT 20'
        );
        $projects->execute(['uid' => $userId, 'q' => $like, 'q2' => $like]);

        $questions = $pdo->prepare(
            'SELECT q.id, q.prompt, p.id AS project_id, p.title AS project_title
             FROM questions q INNER JOIN projects p ON p.id = q.project_id
             WHERE p.user_id = :uid AND q.prompt LIKE :q LIMIT 20'
        );
        $questions->execute(['uid' => $userId, 'q' => $like]);

        $sources = $pdo->prepare(
            'SELECT s.id, s.title, p.id AS project_id, p.title AS project_title
             FROM sources s INNER JOIN projects p ON p.id = s.project_id
             WHERE p.user_id = :uid AND (s.title LIKE :q OR s.authors LIKE :q2) LIMIT 20'
        );
        $sources->execute(['uid' => $userId, 'q' => $like, 'q2' => $like]);

        $claims = $pdo->prepare(
            'SELECT c.id, c.statement, p.id AS project_id, p.title AS project_title
             FROM claims c INNER JOIN projects p ON p.id = c.project_id
             WHERE p.user_id = :uid AND c.statement LIKE :q LIMIT 20'
        );
        $claims->execute(['uid' => $userId, 'q' => $like]);

        $quotes = $pdo->prepare(
            'SELECT qq.id, qq.excerpt, p.id AS project_id, p.title AS project_title
             FROM quotes qq INNER JOIN projects p ON p.id = qq.project_id
             WHERE p.user_id = :uid AND qq.excerpt LIKE :q LIMIT 20'
        );
        $quotes->execute(['uid' => $userId, 'q' => $like]);

        return [
            'projects' => $projects->fetchAll(),
            'questions' => $questions->fetchAll(),
            'sources' => $sources->fetchAll(),
            'claims' => $claims->fetchAll(),
            'quotes' => $quotes->fetchAll(),
        ];
    }

    public static function forProject(int $projectId): array
    {
        $pdo = Database::pdo();
        $count = static function (string $sql, array $params = []) use ($pdo): int {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return (int) $stmt->fetchColumn();
        };

        $questions = $count('SELECT COUNT(*) FROM questions WHERE project_id = :pid', ['pid' => $projectId]);
        $questionsOpen = $count(
            "SELECT COUNT(*) FROM questions WHERE project_id = :pid AND status IN ('open','in_progress')",
            ['pid' => $projectId]
        );
        $questionsAnswered = $count(
            "SELECT COUNT(*) FROM questions WHERE project_id = :pid AND status = 'answered'",
            ['pid' => $projectId]
        );
        $sources = $count('SELECT COUNT(*) FROM sources WHERE project_id = :pid', ['pid' => $projectId]);
        $sourcesUnread = $count(
            "SELECT COUNT(*) FROM sources WHERE project_id = :pid AND reading_status IN ('to_read','reading')",
            ['pid' => $projectId]
        );
        $claims = $count('SELECT COUNT(*) FROM claims WHERE project_id = :pid', ['pid' => $projectId]);
        $claimsSupported = $count(
            "SELECT COUNT(*) FROM claims WHERE project_id = :pid AND status = 'supported'",
            ['pid' => $projectId]
        );
        $claimsDisputed = $count(
            "SELECT COUNT(*) FROM claims WHERE project_id = :pid AND status IN ('disputed','hypothesis')",
            ['pid' => $projectId]
        );
        $quotes = $count('SELECT COUNT(*) FROM quotes WHERE project_id = :pid', ['pid' => $projectId]);
        $evidence = $count('SELECT COUNT(*) FROM evidence_items WHERE project_id = :pid', ['pid' => $projectId]);
        $evidenceFor = $count(
            "SELECT COUNT(*) FROM evidence_items WHERE project_id = :pid AND direction = 'supports'",
            ['pid' => $projectId]
        );
        $evidenceAgainst = $count(
            "SELECT COUNT(*) FROM evidence_items WHERE project_id = :pid AND direction = 'against'",
            ['pid' => $projectId]
        );
        $conclusions = $count('SELECT COUNT(*) FROM conclusions WHERE project_id = :pid', ['pid' => $projectId]);
        $dossiers = $count('SELECT COUNT(*) FROM dossiers WHERE project_id = :pid', ['pid' => $projectId]);

        $claimsWithEvidence = $count(
            'SELECT COUNT(DISTINCT claim_id) FROM evidence_items WHERE project_id = :pid',
            ['pid' => $projectId]
        );
        $claimsWithoutEvidence = max(0, $claims - $claimsWithEvidence);
        $quotesWithoutLocator = $count(
            "SELECT COUNT(*) FROM quotes WHERE project_id = :pid AND (locator IS NULL OR locator = '')",
            ['pid' => $projectId]
        );
        $questionsWithoutConclusion = $count(
            "SELECT COUNT(*) FROM questions q
             WHERE q.project_id = :pid
               AND q.status <> 'discarded'
               AND NOT EXISTS (SELECT 1 FROM conclusions c WHERE c.question_id = q.id)",
            ['pid' => $projectId]
        );

        $coverageQuestions = $questions > 0 ? (int) round(($questionsAnswered / $questions) * 100) : 0;
        $coverageClaims = $claims > 0 ? (int) round(($claimsWithEvidence / $claims) * 100) : 0;

        $avgConfidence = $pdo->prepare(
            'SELECT AVG(confidence) FROM claims WHERE project_id = :pid'
        );
        $avgConfidence->execute(['pid' => $projectId]);
        $avgConf = $avgConfidence->fetchColumn();

        $groupMap = static function (string $sql, array $labelMap, array $params) use ($pdo): array {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll();
            $out = [];
            foreach ($rows as $row) {
                $key = (string) ($row['k'] ?? '');
                $out[] = [
                    'key' => $key,
                    'label' => $labelMap[$key] ?? ($key !== '' ? $key : 'Sin dato'),
                    'value' => (int) ($row['n'] ?? 0),
                ];
            }
            return $out;
        };

        $claimStatusLabels = claim_statuses();
        $questionStatusLabels = question_statuses();
        $readingLabels = reading_statuses();
        $directionLabels = evidence_directions();
        $sourceTypeLabels = source_types();
        $strengthLabels = evidence_strengths();

        $claimsByStatus = $groupMap(
            'SELECT status AS k, COUNT(*) AS n FROM claims WHERE project_id = :pid GROUP BY status ORDER BY n DESC',
            $claimStatusLabels,
            ['pid' => $projectId]
        );
        $questionsByStatus = $groupMap(
            'SELECT status AS k, COUNT(*) AS n FROM questions WHERE project_id = :pid GROUP BY status ORDER BY n DESC',
            $questionStatusLabels,
            ['pid' => $projectId]
        );
        $sourcesByReading = $groupMap(
            'SELECT reading_status AS k, COUNT(*) AS n FROM sources WHERE project_id = :pid GROUP BY reading_status ORDER BY n DESC',
            $readingLabels,
            ['pid' => $projectId]
        );
        $evidenceByDirection = $groupMap(
            'SELECT direction AS k, COUNT(*) AS n FROM evidence_items WHERE project_id = :pid GROUP BY direction ORDER BY n DESC',
            $directionLabels,
            ['pid' => $projectId]
        );
        $sourcesByType = $groupMap(
            'SELECT source_type AS k, COUNT(*) AS n FROM sources WHERE project_id = :pid GROUP BY source_type ORDER BY n DESC',
            $sourceTypeLabels,
            ['pid' => $projectId]
        );
        $evidenceByStrength = $groupMap(
            'SELECT strength AS k, COUNT(*) AS n FROM evidence_items WHERE project_id = :pid GROUP BY strength ORDER BY n DESC',
            $strengthLabels,
            ['pid' => $projectId]
        );

        // Confidence histogram buckets
        $confBuckets = [
            ['label' => '0–20', 'value' => 0],
            ['label' => '21–40', 'value' => 0],
            ['label' => '41–60', 'value' => 0],
            ['label' => '61–80', 'value' => 0],
            ['label' => '81–100', 'value' => 0],
        ];
        $confStmt = $pdo->prepare('SELECT confidence FROM claims WHERE project_id = :pid');
        $confStmt->execute(['pid' => $projectId]);
        foreach ($confStmt->fetchAll() as $row) {
            $v = (int) $row['confidence'];
            if ($v <= 20) {
                $confBuckets[0]['value']++;
            } elseif ($v <= 40) {
                $confBuckets[1]['value']++;
            } elseif ($v <= 60) {
                $confBuckets[2]['value']++;
            } elseif ($v <= 80) {
                $confBuckets[3]['value']++;
            } else {
                $confBuckets[4]['value']++;
            }
        }

        // Activity timeline last 14 days (union of entity creates)
        $timeline = [];
        for ($i = 13; $i >= 0; $i--) {
            $day = date('Y-m-d', strtotime('-' . $i . ' days'));
            $timeline[$day] = [
                'label' => date('d/m', strtotime($day)),
                'day' => $day,
                'questions' => 0,
                'sources' => 0,
                'claims' => 0,
                'evidence' => 0,
                'quotes' => 0,
                'total' => 0,
            ];
        }
        $activitySql = [
            'questions' => 'SELECT DATE(created_at) AS d, COUNT(*) AS n FROM questions WHERE project_id = :pid AND created_at >= DATE_SUB(CURDATE(), INTERVAL 13 DAY) GROUP BY DATE(created_at)',
            'sources' => 'SELECT DATE(created_at) AS d, COUNT(*) AS n FROM sources WHERE project_id = :pid AND created_at >= DATE_SUB(CURDATE(), INTERVAL 13 DAY) GROUP BY DATE(created_at)',
            'claims' => 'SELECT DATE(created_at) AS d, COUNT(*) AS n FROM claims WHERE project_id = :pid AND created_at >= DATE_SUB(CURDATE(), INTERVAL 13 DAY) GROUP BY DATE(created_at)',
            'evidence' => 'SELECT DATE(created_at) AS d, COUNT(*) AS n FROM evidence_items WHERE project_id = :pid AND created_at >= DATE_SUB(CURDATE(), INTERVAL 13 DAY) GROUP BY DATE(created_at)',
            'quotes' => 'SELECT DATE(created_at) AS d, COUNT(*) AS n FROM quotes WHERE project_id = :pid AND created_at >= DATE_SUB(CURDATE(), INTERVAL 13 DAY) GROUP BY DATE(created_at)',
        ];
        foreach ($activitySql as $key => $sql) {
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['pid' => $projectId]);
            foreach ($stmt->fetchAll() as $row) {
                $d = (string) $row['d'];
                if (!isset($timeline[$d])) {
                    continue;
                }
                $timeline[$d][$key] = (int) $row['n'];
                $timeline[$d]['total'] += (int) $row['n'];
            }
        }
        $timeline = array_values($timeline);

        $inventory = [
            ['label' => 'Preguntas', 'value' => $questions],
            ['label' => 'Fuentes', 'value' => $sources],
            ['label' => 'Claims', 'value' => $claims],
            ['label' => 'Citas', 'value' => $quotes],
            ['label' => 'Evidencias', 'value' => $evidence],
            ['label' => 'Conclusiones', 'value' => $conclusions],
            ['label' => 'Dossiers', 'value' => $dossiers],
        ];

        $radar = [
            ['label' => 'Preguntas', 'value' => $coverageQuestions],
            ['label' => 'Evidencia', 'value' => $coverageClaims],
            ['label' => 'Lectura', 'value' => $sources > 0 ? (int) round((($sources - $sourcesUnread) / $sources) * 100) : 0],
            ['label' => 'Confianza', 'value' => $avgConf !== null ? (int) round((float) $avgConf) : 0],
            ['label' => 'Conclusiones', 'value' => $questions > 0 ? (int) round((($questions - $questionsWithoutConclusion) / $questions) * 100) : 0],
            ['label' => 'Balance', 'value' => $evidence > 0 ? (int) round((min($evidenceFor, $evidenceAgainst) / max($evidenceFor, $evidenceAgainst, 1)) * 100) : 0],
        ];

        return [
            'counts' => [
                'questions' => $questions,
                'questions_open' => $questionsOpen,
                'questions_answered' => $questionsAnswered,
                'sources' => $sources,
                'sources_unread' => $sourcesUnread,
                'claims' => $claims,
                'claims_supported' => $claimsSupported,
                'claims_disputed' => $claimsDisputed,
                'claims_with_evidence' => $claimsWithEvidence,
                'claims_without_evidence' => $claimsWithoutEvidence,
                'quotes' => $quotes,
                'quotes_without_locator' => $quotesWithoutLocator,
                'evidence' => $evidence,
                'evidence_for' => $evidenceFor,
                'evidence_against' => $evidenceAgainst,
                'conclusions' => $conclusions,
                'questions_without_conclusion' => $questionsWithoutConclusion,
                'dossiers' => $dossiers,
            ],
            'coverage_questions' => $coverageQuestions,
            'coverage_claims' => $coverageClaims,
            'avg_confidence' => $avgConf !== null ? (int) round((float) $avgConf) : null,
            'integrity' => [
                [
                    'ok' => $claimsWithoutEvidence === 0,
                    'label' => $claimsWithoutEvidence === 0
                        ? 'Todas las afirmaciones tienen evidencia'
                        : $claimsWithoutEvidence . ' afirmación(es) sin evidencia',
                ],
                [
                    'ok' => $quotesWithoutLocator === 0,
                    'label' => $quotesWithoutLocator === 0
                        ? 'Todas las citas tienen localizador'
                        : $quotesWithoutLocator . ' cita(s) sin página/timestamp',
                ],
                [
                    'ok' => $questionsWithoutConclusion === 0,
                    'label' => $questionsWithoutConclusion === 0
                        ? 'Todas las preguntas activas tienen conclusión'
                        : $questionsWithoutConclusion . ' pregunta(s) sin conclusión',
                ],
                [
                    'ok' => $sourcesUnread === 0 || $sources === 0,
                    'label' => $sourcesUnread === 0
                        ? 'Sin fuentes pendientes de lectura'
                        : $sourcesUnread . ' fuente(s) por leer / en lectura',
                ],
            ],
            'charts' => [
                'inventory' => $inventory,
                'claims_by_status' => $claimsByStatus,
                'questions_by_status' => $questionsByStatus,
                'sources_by_reading' => $sourcesByReading,
                'evidence_by_direction' => $evidenceByDirection,
                'sources_by_type' => $sourcesByType,
                'evidence_by_strength' => $evidenceByStrength,
                'confidence_buckets' => $confBuckets,
                'timeline' => $timeline,
                'radar' => $radar,
                'coverage' => [
                    ['label' => 'Preguntas', 'value' => $coverageQuestions],
                    ['label' => 'Claims+evid.', 'value' => $coverageClaims],
                    ['label' => 'Confianza', 'value' => $avgConf !== null ? (int) round((float) $avgConf) : 0],
                ],
            ],
        ];
    }
}
