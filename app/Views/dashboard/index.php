<section class="page-head">
    <div>
        <p class="eyebrow">Taller</p>
        <h1 class="page-title">Panel</h1>
        <p class="lede">Estado de tus investigaciones, preguntas abiertas y claims a vigilar.</p>
    </div>
    <a class="btn btn-accent" href="<?= e(url('/investigaciones/nueva')) ?>"><?= icon('add', 16) ?> Nueva investigación</a>
</section>

<section class="metric-grid metric-grid-6">
    <article class="metric-tile"><span class="metric-label">Investigaciones</span><strong class="metric-value"><?= format_number($stats['projects']) ?></strong></article>
    <article class="metric-tile accent-source"><span class="metric-label">Preguntas</span><strong class="metric-value"><?= format_number($stats['questions']) ?></strong></article>
    <article class="metric-tile accent-copper"><span class="metric-label">Fuentes</span><strong class="metric-value"><?= format_number($stats['sources']) ?></strong></article>
    <article class="metric-tile accent-moss"><span class="metric-label">Afirmaciones</span><strong class="metric-value"><?= format_number($stats['claims']) ?></strong></article>
    <article class="metric-tile accent-crimson"><span class="metric-label">Evidencias</span><strong class="metric-value"><?= format_number($stats['evidence']) ?></strong></article>
    <article class="metric-tile"><span class="metric-label">Dossiers</span><strong class="metric-value"><?= format_number($stats['dossiers']) ?></strong></article>
</section>

<div class="split-panels">
    <section class="panel">
        <div class="panel-head">
            <h2>Investigaciones recientes</h2>
            <a href="<?= e(url('/investigaciones')) ?>">Ver todas</a>
        </div>
        <?php if (empty($stats['recent_projects'])): ?>
            <p class="empty">Todavía no hay investigaciones. Creá la primera para empezar el grafo de evidencia.</p>
        <?php else: ?>
            <ul class="entity-list">
                <?php foreach ($stats['recent_projects'] as $p): ?>
                    <li>
                        <a class="entity-row" href="<?= e(url('/investigaciones/' . $p['id'])) ?>">
                            <div>
                                <strong><?= e($p['title']) ?></strong>
                                <span class="muted"><?= e(format_dt($p['updated_at'])) ?></span>
                            </div>
                            <span class="badge <?= e(status_badge_class($p['status'])) ?>"><?= e(project_status_label($p['status'])) ?></span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>

    <section class="panel">
        <div class="panel-head"><h2>Preguntas abiertas</h2></div>
        <?php if (empty($stats['open_questions'])): ?>
            <p class="empty">Sin preguntas pendientes.</p>
        <?php else: ?>
            <ul class="entity-list">
                <?php foreach ($stats['open_questions'] as $q): ?>
                    <li>
                        <a class="entity-row" href="<?= e(url('/investigaciones/' . $q['project_id'] . '?tab=preguntas')) ?>">
                            <div>
                                <strong><?= e(truncate($q['prompt'], 100)) ?></strong>
                                <span class="muted"><?= e($q['project_title']) ?></span>
                            </div>
                            <span class="badge <?= e(status_badge_class($q['status'])) ?>"><?= e(question_statuses()[$q['status']] ?? $q['status']) ?></span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>
</div>

<section class="panel">
    <div class="panel-head"><h2>Claims a vigilar</h2></div>
    <?php if (empty($stats['watch_claims'])): ?>
        <p class="empty">No hay hipótesis ni disputas activas.</p>
    <?php else: ?>
        <ul class="entity-list">
            <?php foreach ($stats['watch_claims'] as $c): ?>
                <li>
                    <a class="entity-row" href="<?= e(url('/investigaciones/' . $c['project_id'] . '?tab=afirmaciones')) ?>">
                        <div>
                            <strong><?= e(truncate($c['statement'], 120)) ?></strong>
                            <span class="muted"><?= e($c['project_title']) ?></span>
                        </div>
                        <span class="badge <?= e(status_badge_class($c['status'])) ?>"><?= e(claim_statuses()[$c['status']] ?? $c['status']) ?></span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>
