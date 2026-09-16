<?php
/** @var array $project */
/** @var string $tab */
/** @var array $bundle */
$pid = (int) $project['id'];
$tabs = [
    'overview' => 'Overview',
    'metricas' => 'Métricas',
    'preguntas' => 'Preguntas',
    'fuentes' => 'Fuentes',
    'afirmaciones' => 'Afirmaciones',
    'citas' => 'Citas',
    'evidencias' => 'Evidencias',
    'conclusiones' => 'Conclusiones',
    'dossiers' => 'Dossiers',
];
?>
<section class="page-head">
    <div>
        <p class="eyebrow">Investigación</p>
        <h1 class="page-title"><?= e($project['title']) ?></h1>
        <?php if (!empty($project['summary'])): ?>
            <p class="lede"><?= e($project['summary']) ?></p>
        <?php endif; ?>
        <div class="chip-row">
            <span class="badge <?= e(status_badge_class($project['status'])) ?>"><?= e(project_status_label($project['status'])) ?></span>
            <?php if (!empty($project['discipline'])): ?>
                <span class="badge badge-ink"><?= e($project['discipline']) ?></span>
            <?php endif; ?>
        </div>
    </div>
    <div class="page-actions">
        <a class="btn btn-accent" href="<?= e(url('/investigaciones/' . $pid . '/exportar-pdf')) ?>">Exportar PDF</a>
        <a class="btn" href="<?= e(url('/investigaciones/' . $pid . '/editar')) ?>"><?= icon('edit', 14) ?> Editar</a>
        <form method="post" action="<?= e(url('/investigaciones/' . $pid . '/eliminar')) ?>" onsubmit="return confirm('¿Eliminar esta investigación y todo su contenido?');">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-danger"><?= icon('delete', 14) ?> Eliminar</button>
        </form>
    </div>
</section>

<nav class="tabs">
    <?php foreach ($tabs as $key => $label): ?>
        <a class="tab <?= $tab === $key ? 'is-active' : '' ?>" href="<?= e(url('/investigaciones/' . $pid . '?tab=' . $key)) ?>"><?= e($label) ?></a>
    <?php endforeach; ?>
</nav>

<?php if ($tab === 'overview' || $tab === 'metricas'): ?>
    <?php $m = $metrics ?? StatsService::forProject($pid); $c = $m['counts']; ?>
    <section class="metric-grid metric-grid-6">
        <article class="metric-tile"><span class="metric-label">Preguntas</span><strong class="metric-value"><?= format_number($c['questions']) ?></strong></article>
        <article class="metric-tile accent-source"><span class="metric-label">Fuentes</span><strong class="metric-value"><?= format_number($c['sources']) ?></strong></article>
        <article class="metric-tile accent-copper"><span class="metric-label">Claims</span><strong class="metric-value"><?= format_number($c['claims']) ?></strong></article>
        <article class="metric-tile accent-moss"><span class="metric-label">Citas</span><strong class="metric-value"><?= format_number($c['quotes']) ?></strong></article>
        <article class="metric-tile accent-crimson"><span class="metric-label">Evidencias</span><strong class="metric-value"><?= format_number($c['evidence']) ?></strong></article>
        <article class="metric-tile"><span class="metric-label">Dossiers</span><strong class="metric-value"><?= format_number($c['dossiers']) ?></strong></article>
    </section>
<?php endif; ?>

<?php if ($tab === 'overview'): ?>
    <div class="quick-actions">
        <a class="btn" href="<?= e(url('/investigaciones/' . $pid . '/preguntas/nueva')) ?>">+ Pregunta</a>
        <a class="btn" href="<?= e(url('/investigaciones/' . $pid . '/fuentes/nueva')) ?>">+ Fuente</a>
        <a class="btn" href="<?= e(url('/investigaciones/' . $pid . '/afirmaciones/nueva')) ?>">+ Afirmación</a>
        <a class="btn" href="<?= e(url('/investigaciones/' . $pid . '/citas/nueva')) ?>">+ Cita</a>
        <a class="btn" href="<?= e(url('/investigaciones/' . $pid . '/evidencias/nueva')) ?>">+ Evidencia</a>
        <a class="btn" href="<?= e(url('/investigaciones/' . $pid . '/conclusiones/nueva')) ?>">+ Conclusión</a>
        <a class="btn btn-accent" href="<?= e(url('/investigaciones/' . $pid . '/dossiers/nuevo')) ?>">+ Dossier</a>
        <a class="btn" href="<?= e(url('/investigaciones/' . $pid . '?tab=metricas')) ?>">Ver métricas</a>
    </div>
<?php endif; ?>

<?php if ($tab === 'metricas'): ?>
    <?php
    $m = $metrics ?? StatsService::forProject($pid);
    $c = $m['counts'];
    $charts = $m['charts'] ?? [];
    $chartJson = json_encode($charts, JSON_UNESCAPED_UNICODE);
    ?>
    <div class="split-panels">
        <section class="panel">
            <h2>Cobertura</h2>
            <div class="coverage-list">
                <div class="coverage-row">
                    <span>Preguntas respondidas</span>
                    <strong><?= (int) $m['coverage_questions'] ?>%</strong>
                </div>
                <div class="bar"><span style="width:<?= (int) $m['coverage_questions'] ?>%"></span></div>
                <div class="coverage-row">
                    <span>Claims con evidencia</span>
                    <strong><?= (int) $m['coverage_claims'] ?>%</strong>
                </div>
                <div class="bar"><span style="width:<?= (int) $m['coverage_claims'] ?>%"></span></div>
                <div class="coverage-row">
                    <span>Confianza promedio</span>
                    <strong><?= $m['avg_confidence'] !== null ? (int) $m['avg_confidence'] . '%' : '—' ?></strong>
                </div>
            </div>
            <ul class="plain-list metric-detail">
                <li>Preguntas abiertas: <?= format_number($c['questions_open']) ?></li>
                <li>Preguntas respondidas: <?= format_number($c['questions_answered']) ?></li>
                <li>Claims respaldados: <?= format_number($c['claims_supported']) ?></li>
                <li>Claims en disputa / hipótesis: <?= format_number($c['claims_disputed']) ?></li>
                <li>Evidencia a favor: <?= format_number($c['evidence_for']) ?> · en contra: <?= format_number($c['evidence_against']) ?></li>
                <li>Conclusiones: <?= format_number($c['conclusions']) ?></li>
            </ul>
        </section>
        <section class="panel">
            <h2>Integridad</h2>
            <ul class="integrity-list">
                <?php foreach ($m['integrity'] as $item): ?>
                    <li class="<?= $item['ok'] ? 'is-ok' : 'is-warn' ?>">
                        <span class="integrity-mark"><?= $item['ok'] ? 'OK' : '!' ?></span>
                        <?= e($item['label']) ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
    </div>

    <div id="metrics-charts" class="charts-board" data-charts='<?= e($chartJson ?: '{}') ?>'>
        <section class="panel chart-panel">
            <div class="panel-head"><h2>Inventario</h2><span class="muted">Barras</span></div>
            <canvas data-chart="bar" data-series="inventory" height="220" aria-label="Inventario por tipo"></canvas>
        </section>
        <section class="panel chart-panel">
            <div class="panel-head"><h2>Estados de claims</h2><span class="muted">Torta</span></div>
            <canvas data-chart="pie" data-series="claims_by_status" height="220" aria-label="Claims por estado"></canvas>
        </section>
        <section class="panel chart-panel">
            <div class="panel-head"><h2>Dirección de evidencia</h2><span class="muted">Dona</span></div>
            <canvas data-chart="donut" data-series="evidence_by_direction" height="220" aria-label="Evidencia por dirección"></canvas>
        </section>
        <section class="panel chart-panel">
            <div class="panel-head"><h2>Preguntas por estado</h2><span class="muted">Barras horizontales</span></div>
            <canvas data-chart="hbar" data-series="questions_by_status" height="220" aria-label="Preguntas por estado"></canvas>
        </section>
        <section class="panel chart-panel chart-panel-wide">
            <div class="panel-head"><h2>Actividad (14 días)</h2><span class="muted">Líneas</span></div>
            <canvas data-chart="line-multi" data-series="timeline" height="260" aria-label="Actividad reciente"></canvas>
        </section>
        <section class="panel chart-panel">
            <div class="panel-head"><h2>Distribución de confianza</h2><span class="muted">Histograma</span></div>
            <canvas data-chart="bar" data-series="confidence_buckets" height="220" aria-label="Confianza de claims"></canvas>
        </section>
        <section class="panel chart-panel">
            <div class="panel-head"><h2>Fuentes por tipo</h2><span class="muted">Barras</span></div>
            <canvas data-chart="bar" data-series="sources_by_type" height="220" aria-label="Fuentes por tipo"></canvas>
        </section>
        <section class="panel chart-panel">
            <div class="panel-head"><h2>Fuerza de evidencia</h2><span class="muted">Torta</span></div>
            <canvas data-chart="pie" data-series="evidence_by_strength" height="220" aria-label="Fuerza de evidencia"></canvas>
        </section>
        <section class="panel chart-panel">
            <div class="panel-head"><h2>Lectura de fuentes</h2><span class="muted">Dona</span></div>
            <canvas data-chart="donut" data-series="sources_by_reading" height="220" aria-label="Estado de lectura"></canvas>
        </section>
        <section class="panel chart-panel">
            <div class="panel-head"><h2>Salud de la investigación</h2><span class="muted">Radar</span></div>
            <canvas data-chart="radar" data-series="radar" height="260" aria-label="Radar de salud"></canvas>
        </section>
        <section class="panel chart-panel">
            <div class="panel-head"><h2>Cobertura comparada</h2><span class="muted">Barras %</span></div>
            <canvas data-chart="bar" data-series="coverage" data-max="100" height="220" aria-label="Cobertura"></canvas>
        </section>
    </div>
<?php endif; ?>

<?php if ($tab === 'preguntas'): ?>
    <div class="section-toolbar">
        <h2>Preguntas</h2>
        <a class="btn btn-accent btn-sm" href="<?= e(url('/investigaciones/' . $pid . '/preguntas/nueva')) ?>">Agregar</a>
    </div>
    <?php if (empty($bundle['questions'])): ?>
        <section class="panel"><p class="empty">Sin preguntas.</p></section>
    <?php else: ?>
        <ul class="entity-list panel">
            <?php foreach ($bundle['questions'] as $q): ?>
                <li class="entity-row static">
                    <div>
                        <strong><?= e($q['prompt']) ?></strong>
                        <span class="muted"><?= e(question_kinds()[$q['kind']] ?? $q['kind']) ?> · prioridad <?= e($q['priority']) ?></span>
                    </div>
                    <div class="row-actions">
                        <span class="badge <?= e(status_badge_class($q['status'])) ?>"><?= e(question_statuses()[$q['status']] ?? $q['status']) ?></span>
                        <a class="btn btn-sm" href="<?= e(url('/investigaciones/' . $pid . '/preguntas/' . $q['id'] . '/editar')) ?>">Editar</a>
                        <form method="post" action="<?= e(url('/investigaciones/' . $pid . '/preguntas/' . $q['id'] . '/eliminar')) ?>" onsubmit="return confirm('¿Eliminar?');">
                            <?= csrf_field() ?>
                            <button class="btn btn-sm btn-danger" type="submit">Borrar</button>
                        </form>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
<?php endif; ?>

<?php if ($tab === 'fuentes'): ?>
    <div class="section-toolbar">
        <h2>Fuentes</h2>
        <a class="btn btn-accent btn-sm" href="<?= e(url('/investigaciones/' . $pid . '/fuentes/nueva')) ?>">Agregar</a>
    </div>
    <?php if (empty($bundle['sources'])): ?>
        <section class="panel"><p class="empty">Sin fuentes.</p></section>
    <?php else: ?>
        <ul class="entity-list panel">
            <?php foreach ($bundle['sources'] as $s): ?>
                <li class="entity-row static">
                    <div>
                        <strong><?= e($s['title']) ?></strong>
                        <span class="muted">
                            <?= e(source_types()[$s['source_type']] ?? $s['source_type']) ?>
                            <?php if (!empty($s['authors'])): ?> · <?= e($s['authors']) ?><?php endif; ?>
                            <?php if (!empty($s['year'])): ?> · <?= (int) $s['year'] ?><?php endif; ?>
                        </span>
                    </div>
                    <div class="row-actions">
                        <span class="badge <?= e(status_badge_class($s['reading_status'])) ?>"><?= e(reading_statuses()[$s['reading_status']] ?? $s['reading_status']) ?></span>
                        <a class="btn btn-sm" href="<?= e(url('/investigaciones/' . $pid . '/fuentes/' . $s['id'] . '/editar')) ?>">Editar</a>
                        <form method="post" action="<?= e(url('/investigaciones/' . $pid . '/fuentes/' . $s['id'] . '/eliminar')) ?>" onsubmit="return confirm('¿Eliminar?');">
                            <?= csrf_field() ?>
                            <button class="btn btn-sm btn-danger" type="submit">Borrar</button>
                        </form>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
<?php endif; ?>

<?php if ($tab === 'afirmaciones'): ?>
    <div class="section-toolbar">
        <h2>Afirmaciones</h2>
        <a class="btn btn-accent btn-sm" href="<?= e(url('/investigaciones/' . $pid . '/afirmaciones/nueva')) ?>">Agregar</a>
    </div>
    <?php if (empty($bundle['claims'])): ?>
        <section class="panel"><p class="empty">Sin afirmaciones.</p></section>
    <?php else: ?>
        <ul class="entity-list panel">
            <?php foreach ($bundle['claims'] as $c): ?>
                <li class="entity-row static">
                    <div>
                        <strong><?= e($c['statement']) ?></strong>
                        <span class="muted">
                            <?= e(claim_types()[$c['claim_type']] ?? $c['claim_type']) ?> · confianza <?= (int) $c['confidence'] ?>%
                            · a favor <?= (int) $c['support_count'] ?> / en contra <?= (int) $c['against_count'] ?>
                        </span>
                    </div>
                    <div class="row-actions">
                        <span class="badge <?= e(status_badge_class($c['status'])) ?>"><?= e(claim_statuses()[$c['status']] ?? $c['status']) ?></span>
                        <a class="btn btn-sm" href="<?= e(url('/investigaciones/' . $pid . '/afirmaciones/' . $c['id'] . '/editar')) ?>">Editar</a>
                        <form method="post" action="<?= e(url('/investigaciones/' . $pid . '/afirmaciones/' . $c['id'] . '/eliminar')) ?>" onsubmit="return confirm('¿Eliminar?');">
                            <?= csrf_field() ?>
                            <button class="btn btn-sm btn-danger" type="submit">Borrar</button>
                        </form>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
<?php endif; ?>

<?php if ($tab === 'citas'): ?>
    <div class="section-toolbar">
        <h2>Citas</h2>
        <a class="btn btn-accent btn-sm" href="<?= e(url('/investigaciones/' . $pid . '/citas/nueva')) ?>">Agregar</a>
    </div>
    <?php if (empty($bundle['quotes'])): ?>
        <section class="panel"><p class="empty">Sin citas.</p></section>
    <?php else: ?>
        <ul class="entity-list panel">
            <?php foreach ($bundle['quotes'] as $q): ?>
                <li class="entity-row static">
                    <div>
                        <blockquote class="quote-block"><?= e(truncate($q['excerpt'], 220)) ?></blockquote>
                        <span class="muted"><?= e($q['source_title']) ?><?= !empty($q['locator']) ? ' · ' . e($q['locator']) : '' ?></span>
                    </div>
                    <div class="row-actions">
                        <a class="btn btn-sm" href="<?= e(url('/investigaciones/' . $pid . '/citas/' . $q['id'] . '/editar')) ?>">Editar</a>
                        <form method="post" action="<?= e(url('/investigaciones/' . $pid . '/citas/' . $q['id'] . '/eliminar')) ?>" onsubmit="return confirm('¿Eliminar?');">
                            <?= csrf_field() ?>
                            <button class="btn btn-sm btn-danger" type="submit">Borrar</button>
                        </form>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
<?php endif; ?>

<?php if ($tab === 'evidencias'): ?>
    <div class="section-toolbar">
        <h2>Evidencias</h2>
        <a class="btn btn-accent btn-sm" href="<?= e(url('/investigaciones/' . $pid . '/evidencias/nueva')) ?>">Agregar</a>
    </div>
    <?php if (empty($bundle['evidence'])): ?>
        <section class="panel"><p class="empty">Sin evidencias.</p></section>
    <?php else: ?>
        <ul class="entity-list panel">
            <?php foreach ($bundle['evidence'] as $ev): ?>
                <li class="entity-row static">
                    <div>
                        <strong><?= e($ev['summary']) ?></strong>
                        <span class="muted"><?= e(truncate($ev['claim_statement'] ?? '', 100)) ?></span>
                    </div>
                    <div class="row-actions">
                        <span class="badge <?= e(direction_badge_class($ev['direction'])) ?>"><?= e(evidence_directions()[$ev['direction']] ?? $ev['direction']) ?></span>
                        <span class="badge badge-slate"><?= e(evidence_strengths()[$ev['strength']] ?? $ev['strength']) ?></span>
                        <a class="btn btn-sm" href="<?= e(url('/investigaciones/' . $pid . '/evidencias/' . $ev['id'] . '/editar')) ?>">Editar</a>
                        <form method="post" action="<?= e(url('/investigaciones/' . $pid . '/evidencias/' . $ev['id'] . '/eliminar')) ?>" onsubmit="return confirm('¿Eliminar?');">
                            <?= csrf_field() ?>
                            <button class="btn btn-sm btn-danger" type="submit">Borrar</button>
                        </form>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
<?php endif; ?>

<?php if ($tab === 'conclusiones'): ?>
    <div class="section-toolbar">
        <h2>Conclusiones</h2>
        <a class="btn btn-accent btn-sm" href="<?= e(url('/investigaciones/' . $pid . '/conclusiones/nueva')) ?>">Agregar</a>
    </div>
    <?php if (empty($bundle['conclusions'])): ?>
        <section class="panel"><p class="empty">Sin conclusiones.</p></section>
    <?php else: ?>
        <ul class="entity-list panel">
            <?php foreach ($bundle['conclusions'] as $c): ?>
                <li class="entity-row static">
                    <div>
                        <strong><?= e($c['title']) ?></strong>
                        <span class="muted"><?= e(truncate($c['body'], 140)) ?></span>
                    </div>
                    <div class="row-actions">
                        <span class="badge <?= e(status_badge_class($c['conclusion_type'])) ?>"><?= e(conclusion_types()[$c['conclusion_type']] ?? $c['conclusion_type']) ?></span>
                        <a class="btn btn-sm" href="<?= e(url('/investigaciones/' . $pid . '/conclusiones/' . $c['id'] . '/editar')) ?>">Editar</a>
                        <form method="post" action="<?= e(url('/investigaciones/' . $pid . '/conclusiones/' . $c['id'] . '/eliminar')) ?>" onsubmit="return confirm('¿Eliminar?');">
                            <?= csrf_field() ?>
                            <button class="btn btn-sm btn-danger" type="submit">Borrar</button>
                        </form>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
<?php endif; ?>

<?php if ($tab === 'dossiers'): ?>
    <div class="section-toolbar">
        <h2>Dossiers</h2>
        <a class="btn btn-accent btn-sm" href="<?= e(url('/investigaciones/' . $pid . '/dossiers/nuevo')) ?>">Agregar</a>
    </div>
    <?php if (empty($bundle['dossiers'])): ?>
        <section class="panel"><p class="empty">Sin dossiers. Armá uno desde las piezas del grafo.</p></section>
    <?php else: ?>
        <ul class="entity-list panel">
            <?php foreach ($bundle['dossiers'] as $d): ?>
                <li class="entity-row static">
                    <div>
                        <strong><a href="<?= e(url('/investigaciones/' . $pid . '/dossiers/' . $d['id'])) ?>"><?= e($d['title']) ?></a></strong>
                        <span class="muted"><?= e(dossier_modes()[$d['mode']] ?? $d['mode']) ?> · <?= (int) $d['items_count'] ?> piezas</span>
                    </div>
                    <div class="row-actions">
                        <span class="badge <?= e(status_badge_class($d['status'])) ?>"><?= e(dossier_statuses()[$d['status']] ?? $d['status']) ?></span>
                        <a class="btn btn-sm" href="<?= e(url('/investigaciones/' . $pid . '/dossiers/' . $d['id'] . '/editar')) ?>">Editar</a>
                        <form method="post" action="<?= e(url('/investigaciones/' . $pid . '/dossiers/' . $d['id'] . '/eliminar')) ?>" onsubmit="return confirm('¿Eliminar?');">
                            <?= csrf_field() ?>
                            <button class="btn btn-sm btn-danger" type="submit">Borrar</button>
                        </form>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
<?php endif; ?>
