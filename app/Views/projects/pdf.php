<?php
/** @var array $project */
/** @var array $bundle */
/** @var array $metrics */
/** @var array|null $user */
/** @var string $generatedAt */
$charts = $metrics['charts'] ?? [];
$c = $metrics['counts'] ?? [];
$pid = (int) $project['id'];
?>
<div class="print-toolbar no-print">
    <a class="btn" href="<?= e(url('/investigaciones/' . $pid)) ?>">← Volver</a>
    <div class="print-toolbar-actions">
        <button type="button" class="btn btn-accent" onclick="window.print()">Imprimir / Guardar PDF</button>
        <a class="btn" href="<?= e(url('/investigaciones/' . $pid . '/exportar-pdf?print=1')) ?>">Abrir diálogo PDF</a>
    </div>
</div>

<article class="print-report">
    <header class="print-cover">
        <p class="eyebrow">ResearchForge · Informe de investigación</p>
        <h1><?= e($project['title']) ?></h1>
        <?php if (!empty($project['summary'])): ?>
            <p class="print-summary"><?= e($project['summary']) ?></p>
        <?php endif; ?>
        <div class="print-meta">
            <span>Estado: <?= e(project_status_label($project['status'])) ?></span>
            <?php if (!empty($project['discipline'])): ?>
                <span>Disciplina: <?= e($project['discipline']) ?></span>
            <?php endif; ?>
            <span>Generado: <?= e($generatedAt) ?></span>
            <span>Por: <?= e($user['name'] ?? '') ?></span>
        </div>
    </header>

    <section class="print-section">
        <h2>Resumen numérico</h2>
        <div class="print-kpi">
            <div><strong><?= format_number($c['questions'] ?? 0) ?></strong><span>Preguntas</span></div>
            <div><strong><?= format_number($c['sources'] ?? 0) ?></strong><span>Fuentes</span></div>
            <div><strong><?= format_number($c['claims'] ?? 0) ?></strong><span>Claims</span></div>
            <div><strong><?= format_number($c['quotes'] ?? 0) ?></strong><span>Citas</span></div>
            <div><strong><?= format_number($c['evidence'] ?? 0) ?></strong><span>Evidencias</span></div>
            <div><strong><?= format_number($c['conclusions'] ?? 0) ?></strong><span>Conclusiones</span></div>
            <div><strong><?= format_number($c['dossiers'] ?? 0) ?></strong><span>Dossiers</span></div>
        </div>
        <p class="print-note">
            Cobertura preguntas: <strong><?= (int) ($metrics['coverage_questions'] ?? 0) ?>%</strong>
            · Claims con evidencia: <strong><?= (int) ($metrics['coverage_claims'] ?? 0) ?>%</strong>
            · Confianza promedio: <strong><?= $metrics['avg_confidence'] !== null ? (int) $metrics['avg_confidence'] . '%' : '—' ?></strong>
        </p>
    </section>

    <section class="print-section">
        <h2>Métricas visuales</h2>
        <div class="print-charts">
            <figure>
                <figcaption>Inventario</figcaption>
                <?= ChartSvg::bar($charts['inventory'] ?? []) ?>
            </figure>
            <figure>
                <figcaption>Claims por estado</figcaption>
                <?= ChartSvg::pie($charts['claims_by_status'] ?? []) ?>
            </figure>
            <figure>
                <figcaption>Dirección de evidencia</figcaption>
                <?= ChartSvg::pie($charts['evidence_by_direction'] ?? [], 220, true) ?>
            </figure>
            <figure>
                <figcaption>Preguntas por estado</figcaption>
                <?= ChartSvg::hbar($charts['questions_by_status'] ?? []) ?>
            </figure>
            <figure class="print-chart-wide">
                <figcaption>Actividad (14 días)</figcaption>
                <?= ChartSvg::line($charts['timeline'] ?? [], 720, 220) ?>
            </figure>
            <figure>
                <figcaption>Distribución de confianza</figcaption>
                <?= ChartSvg::bar($charts['confidence_buckets'] ?? []) ?>
            </figure>
            <figure>
                <figcaption>Fuentes por tipo</figcaption>
                <?= ChartSvg::bar($charts['sources_by_type'] ?? []) ?>
            </figure>
            <figure>
                <figcaption>Lectura de fuentes</figcaption>
                <?= ChartSvg::pie($charts['sources_by_reading'] ?? [], 220, true) ?>
            </figure>
        </div>
    </section>

    <?php if (!empty($metrics['integrity'])): ?>
    <section class="print-section">
        <h2>Integridad</h2>
        <ul>
            <?php foreach ($metrics['integrity'] as $item): ?>
                <li><?= $item['ok'] ? '✓' : '!' ?> <?= e($item['label']) ?></li>
            <?php endforeach; ?>
        </ul>
    </section>
    <?php endif; ?>

    <section class="print-section">
        <h2>Preguntas</h2>
        <?php if (empty($bundle['questions'])): ?>
            <p class="muted">Sin preguntas.</p>
        <?php else: ?>
            <ol>
                <?php foreach ($bundle['questions'] as $q): ?>
                    <li>
                        <strong><?= e($q['prompt']) ?></strong>
                        <span class="muted"> — <?= e(question_statuses()[$q['status']] ?? $q['status']) ?> · <?= e(question_kinds()[$q['kind']] ?? $q['kind']) ?></span>
                        <?php if (!empty($q['notes'])): ?><div class="print-notes"><?= e($q['notes']) ?></div><?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ol>
        <?php endif; ?>
    </section>

    <section class="print-section">
        <h2>Fuentes</h2>
        <?php if (empty($bundle['sources'])): ?>
            <p class="muted">Sin fuentes.</p>
        <?php else: ?>
            <ul class="print-list">
                <?php foreach ($bundle['sources'] as $s): ?>
                    <li>
                        <strong><?= e($s['title']) ?></strong>
                        <span class="muted">
                            — <?= e(source_types()[$s['source_type']] ?? $s['source_type']) ?>
                            <?php if (!empty($s['authors'])): ?> · <?= e($s['authors']) ?><?php endif; ?>
                            <?php if (!empty($s['year'])): ?> · <?= (int) $s['year'] ?><?php endif; ?>
                        </span>
                        <?php if (!empty($s['url'])): ?><div class="print-notes"><?= e($s['url']) ?></div><?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>

    <section class="print-section">
        <h2>Afirmaciones</h2>
        <?php if (empty($bundle['claims'])): ?>
            <p class="muted">Sin afirmaciones.</p>
        <?php else: ?>
            <ul class="print-list">
                <?php foreach ($bundle['claims'] as $claim): ?>
                    <li>
                        <strong><?= e($claim['statement']) ?></strong>
                        <span class="muted"> — <?= e(claim_statuses()[$claim['status']] ?? $claim['status']) ?> · confianza <?= (int) $claim['confidence'] ?>%</span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>

    <section class="print-section">
        <h2>Citas</h2>
        <?php if (empty($bundle['quotes'])): ?>
            <p class="muted">Sin citas.</p>
        <?php else: ?>
            <?php foreach ($bundle['quotes'] as $q): ?>
                <blockquote class="print-quote">
                    <?= e($q['excerpt']) ?>
                    <footer>— <?= e($q['source_title'] ?? 'Fuente') ?><?= !empty($q['locator']) ? ', ' . e($q['locator']) : '' ?></footer>
                </blockquote>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>

    <section class="print-section">
        <h2>Evidencias</h2>
        <?php if (empty($bundle['evidence'])): ?>
            <p class="muted">Sin evidencias.</p>
        <?php else: ?>
            <ul class="print-list">
                <?php foreach ($bundle['evidence'] as $ev): ?>
                    <li>
                        <strong>[<?= e(evidence_directions()[$ev['direction']] ?? $ev['direction']) ?> · <?= e(evidence_strengths()[$ev['strength']] ?? $ev['strength']) ?>]</strong>
                        <?= e($ev['summary']) ?>
                        <?php if (!empty($ev['claim_statement'])): ?>
                            <div class="print-notes">Claim: <?= e($ev['claim_statement']) ?></div>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>

    <section class="print-section">
        <h2>Conclusiones</h2>
        <?php if (empty($bundle['conclusions'])): ?>
            <p class="muted">Sin conclusiones.</p>
        <?php else: ?>
            <?php foreach ($bundle['conclusions'] as $con): ?>
                <article class="print-conclusion">
                    <h3><?= e($con['title']) ?></h3>
                    <p><?= nl2br(e($con['body'])) ?></p>
                    <?php if (!empty($con['limitations'])): ?>
                        <p><strong>Limitaciones:</strong> <?= e($con['limitations']) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($con['next_steps'])): ?>
                        <p><strong>Próximos pasos:</strong> <?= e($con['next_steps']) ?></p>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>

    <section class="print-section">
        <h2>Dossiers</h2>
        <?php if (empty($bundle['dossiers'])): ?>
            <p class="muted">Sin dossiers.</p>
        <?php else: ?>
            <ul class="print-list">
                <?php foreach ($bundle['dossiers'] as $d): ?>
                    <li>
                        <strong><?= e($d['title']) ?></strong>
                        <span class="muted"> — <?= e(dossier_modes()[$d['mode']] ?? $d['mode']) ?> · <?= e(dossier_statuses()[$d['status']] ?? $d['status']) ?> · <?= (int) $d['items_count'] ?> piezas</span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>

    <footer class="print-footer">
        Exportado desde ResearchForge · <?= e($generatedAt) ?>
    </footer>
</article>
