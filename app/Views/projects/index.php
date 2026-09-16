<section class="page-head">
    <div>
        <p class="eyebrow">Archivo</p>
        <h1 class="page-title">Investigaciones</h1>
        <p class="lede">Cada investigación es un taller con su propio grafo de evidencia.</p>
    </div>
    <a class="btn btn-accent" href="<?= e(url('/investigaciones/nueva')) ?>"><?= icon('add', 16) ?> Nueva</a>
</section>

<form class="filter-bar" method="get" action="<?= e(url('/investigaciones')) ?>">
    <label>
        <span>Estado</span>
        <select name="status" onchange="this.form.submit()">
            <option value="">Todas</option>
            <?php foreach (project_statuses() as $key => $label): ?>
                <option value="<?= e($key) ?>" <?= $status === $key ? 'selected' : '' ?>><?= e($label) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
</form>

<?php if (empty($projects)): ?>
    <section class="panel"><p class="empty">No hay investigaciones todavía.</p></section>
<?php else: ?>
    <div class="project-grid">
        <?php foreach ($projects as $p): ?>
            <a class="project-card" href="<?= e(url('/investigaciones/' . $p['id'])) ?>">
                <div class="project-card-top">
                    <span class="badge <?= e(status_badge_class($p['status'])) ?>"><?= e(project_status_label($p['status'])) ?></span>
                    <span class="muted"><?= e(format_dt($p['updated_at'])) ?></span>
                </div>
                <h2><?= e($p['title']) ?></h2>
                <?php if (!empty($p['summary'])): ?>
                    <p><?= e(truncate($p['summary'], 160)) ?></p>
                <?php endif; ?>
                <div class="project-card-meta">
                    <span><?= (int) $p['questions_count'] ?> preg.</span>
                    <span><?= (int) $p['sources_count'] ?> fuentes</span>
                    <span><?= (int) $p['claims_count'] ?> claims</span>
                    <span><?= (int) $p['dossiers_count'] ?> dossiers</span>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
