<section class="page-head">
    <div>
        <p class="eyebrow"><?= e($project['title']) ?></p>
        <h1 class="page-title"><?= e($item ? 'Editar fuente' : 'Nueva fuente') ?></h1>
    </div>
    <a class="btn" href="<?= e(url('/investigaciones/' . $project['id'] . '?tab=fuentes')) ?>">Volver</a>
</section>
<section class="panel">
<form class="stack-form" method="post" action="<?= e(url($item ? '/investigaciones/' . $project['id'] . '/fuentes/' . $item['id'] : '/investigaciones/' . $project['id'] . '/fuentes')) ?>">
    <?= csrf_field() ?>
    <label class="field"><span>Título</span><input type="text" name="title" required value="<?= e($item['title'] ?? '') ?>"></label>
    <div class="form-grid-2">
        <label class="field"><span>Tipo</span>
            <select name="source_type"><?php foreach (source_types() as $k => $l): ?><option value="<?= e($k) ?>" <?= ($item['source_type'] ?? 'article') === $k ? 'selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?></select>
        </label>
        <label class="field"><span>Estado de lectura</span>
            <select name="reading_status"><?php foreach (reading_statuses() as $k => $l): ?><option value="<?= e($k) ?>" <?= ($item['reading_status'] ?? 'to_read') === $k ? 'selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?></select>
        </label>
        <label class="field"><span>Autores</span><input type="text" name="authors" value="<?= e($item['authors'] ?? '') ?>"></label>
        <label class="field"><span>Año</span><input type="number" name="year" value="<?= e((string)($item['year'] ?? '')) ?>"></label>
        <label class="field"><span>Editorial / medio</span><input type="text" name="publisher" value="<?= e($item['publisher'] ?? '') ?>"></label>
        <label class="field"><span>Confiabilidad (1–5)</span><input type="number" min="1" max="5" name="reliability" value="<?= e((string)($item['reliability'] ?? '')) ?>"></label>
        <label class="field"><span>URL</span><input type="url" name="url" value="<?= e($item['url'] ?? '') ?>"></label>
        <label class="field"><span>DOI</span><input type="text" name="doi" value="<?= e($item['doi'] ?? '') ?>"></label>
        <label class="field"><span>ISBN</span><input type="text" name="isbn" value="<?= e($item['isbn'] ?? '') ?>"></label>
        <label class="field"><span>Fecha de acceso</span><input type="date" name="accessed_at" value="<?= e($item['accessed_at'] ?? '') ?>"></label>
        <label class="field"><span>Idioma</span><input type="text" name="language" value="<?= e($item['language'] ?? '') ?>"></label>
    </div>
    <label class="field"><span>Notas de sesgo</span><textarea name="bias_notes" rows="2"><?= e($item['bias_notes'] ?? '') ?></textarea></label>
    <label class="field"><span>Notas</span><textarea name="notes" rows="3"><?= e($item['notes'] ?? '') ?></textarea></label>
    <button class="btn btn-accent" type="submit">Guardar</button>
</form>
</section>
