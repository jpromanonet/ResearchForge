<section class="page-head">
    <div>
        <p class="eyebrow">Búsqueda</p>
        <h1 class="page-title">Buscar</h1>
    </div>
</section>
<section class="panel">
    <form method="get" action="<?= e(url('/buscar')) ?>" class="stack-form">
        <label class="field">
            <span>Consulta</span>
            <input type="search" name="q" value="<?= e($q) ?>" placeholder="Preguntas, fuentes, claims, citas…" autofocus>
        </label>
        <button class="btn btn-accent" type="submit">Buscar</button>
    </form>
</section>

<?php if ($results !== null): ?>
    <?php
    $blocks = [
        'projects' => 'Investigaciones',
        'questions' => 'Preguntas',
        'sources' => 'Fuentes',
        'claims' => 'Afirmaciones',
        'quotes' => 'Citas',
    ];
    $any = false;
    foreach ($blocks as $key => $label) {
        if (!empty($results[$key])) { $any = true; break; }
    }
    ?>
    <?php if (!$any): ?>
        <section class="panel"><p class="empty">Sin resultados para “<?= e($q) ?>”.</p></section>
    <?php else: ?>
        <?php foreach ($blocks as $key => $label): ?>
            <?php if (empty($results[$key])) continue; ?>
            <section class="panel">
                <h2><?= e($label) ?></h2>
                <ul class="entity-list">
                    <?php foreach ($results[$key] as $row): ?>
                        <li>
                            <?php if ($key === 'projects'): ?>
                                <a class="entity-row" href="<?= e(url('/investigaciones/' . $row['id'])) ?>">
                                    <strong><?= e($row['title']) ?></strong>
                                </a>
                            <?php else: ?>
                                <a class="entity-row" href="<?= e(url('/investigaciones/' . $row['project_id'])) ?>">
                                    <div>
                                        <strong><?= e(truncate($row['prompt'] ?? $row['title'] ?? $row['statement'] ?? $row['excerpt'] ?? '', 120)) ?></strong>
                                        <span class="muted"><?= e($row['project_title'] ?? '') ?></span>
                                    </div>
                                </a>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </section>
        <?php endforeach; ?>
    <?php endif; ?>
<?php endif; ?>
