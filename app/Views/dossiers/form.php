<?php
$pid = (int) $project['id'];
$groups = [
    'questions' => ['label' => 'Preguntas', 'field' => 'prompt'],
    'sources' => ['label' => 'Fuentes', 'field' => 'title'],
    'claims' => ['label' => 'Afirmaciones', 'field' => 'statement'],
    'quotes' => ['label' => 'Citas', 'field' => 'excerpt'],
    'evidence' => ['label' => 'Evidencias', 'field' => 'summary'],
    'conclusions' => ['label' => 'Conclusiones', 'field' => 'title'],
];
?>
<section class="page-head">
    <div>
        <p class="eyebrow"><?= e($project['title']) ?></p>
        <h1 class="page-title"><?= e($item ? 'Editar dossier' : 'Nuevo dossier') ?></h1>
        <p class="lede">Elegí qué piezas del grafo entran al documento exportable.</p>
    </div>
    <a class="btn" href="<?= e(url('/investigaciones/' . $pid . '?tab=dossiers')) ?>">Volver</a>
</section>
<section class="panel">
<form class="stack-form" method="post" action="<?= e(url($item ? '/investigaciones/' . $pid . '/dossiers/' . $item['id'] : '/investigaciones/' . $pid . '/dossiers')) ?>">
    <?= csrf_field() ?>
    <label class="field"><span>Título</span><input type="text" name="title" required value="<?= e($item['title'] ?? ($project['title'] . ' — Dossier')) ?>"></label>
    <label class="field"><span>Subtítulo</span><input type="text" name="subtitle" value="<?= e($item['subtitle'] ?? '') ?>"></label>
    <div class="form-grid-2">
        <label class="field"><span>Modo</span>
            <select name="mode"><?php foreach (dossier_modes() as $k => $l): ?><option value="<?= e($k) ?>" <?= ($item['mode'] ?? 'briefing') === $k ? 'selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?></select>
        </label>
        <label class="field"><span>Estado</span>
            <select name="status"><?php foreach (dossier_statuses() as $k => $l): ?><option value="<?= e($k) ?>" <?= ($item['status'] ?? 'draft') === $k ? 'selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?></select>
        </label>
    </div>
    <label class="field"><span>Resumen ejecutivo</span><textarea name="executive_summary" rows="4"><?= e($item['executive_summary'] ?? '') ?></textarea></label>

    <h3 class="form-section-title">Piezas a incluir</h3>
    <?php foreach ($groups as $key => $meta): ?>
        <fieldset class="include-set">
            <legend><?= e($meta['label']) ?></legend>
            <?php if (empty($bundle[$key])): ?>
                <p class="muted">Sin ítems.</p>
            <?php else: ?>
                <?php foreach ($bundle[$key] as $row): ?>
                    <?php $checked = in_array((int)$row['id'], array_map('intval', $selected[$key] ?? []), true); ?>
                    <label class="check-row">
                        <input type="checkbox" name="include[<?= e($key) ?>][]" value="<?= (int)$row['id'] ?>" <?= $checked ? 'checked' : '' ?>>
                        <span><?= e(truncate((string)$row[$meta['field']], 120)) ?></span>
                    </label>
                <?php endforeach; ?>
            <?php endif; ?>
        </fieldset>
    <?php endforeach; ?>

    <button class="btn btn-accent" type="submit">Guardar dossier</button>
</form>
</section>
