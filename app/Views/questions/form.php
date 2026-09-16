<section class="page-head">
    <div>
        <p class="eyebrow"><?= e($project['title']) ?></p>
        <h1 class="page-title"><?= e($item ? 'Editar pregunta' : 'Nueva pregunta') ?></h1>
    </div>
    <a class="btn" href="<?= e(url('/investigaciones/' . $project['id'] . '?tab=preguntas')) ?>">Volver</a>
</section>
<section class="panel">
<form class="stack-form" method="post" action="<?= e(url($item ? '/investigaciones/' . $project['id'] . '/preguntas/' . $item['id'] : '/investigaciones/' . $project['id'] . '/preguntas')) ?>">
    <?= csrf_field() ?>
    <label class="field"><span>Pregunta</span><textarea name="prompt" rows="3" required><?= e($item['prompt'] ?? '') ?></textarea></label>
    <div class="form-grid-2">
        <label class="field"><span>Tipo</span>
            <select name="kind"><?php foreach (question_kinds() as $k => $l): ?><option value="<?= e($k) ?>" <?= ($item['kind'] ?? 'open') === $k ? 'selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?></select>
        </label>
        <label class="field"><span>Estado</span>
            <select name="status"><?php foreach (question_statuses() as $k => $l): ?><option value="<?= e($k) ?>" <?= ($item['status'] ?? 'open') === $k ? 'selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?></select>
        </label>
        <label class="field"><span>Prioridad</span>
            <select name="priority">
                <?php foreach (['low' => 'Baja', 'medium' => 'Media', 'high' => 'Alta'] as $k => $l): ?>
                    <option value="<?= e($k) ?>" <?= ($item['priority'] ?? 'medium') === $k ? 'selected' : '' ?>><?= e($l) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label class="field"><span>Padre (opcional)</span>
            <select name="parent_id">
                <option value="">—</option>
                <?php foreach ($questions as $q): if ($item && (int)$q['id'] === (int)$item['id']) continue; ?>
                    <option value="<?= (int)$q['id'] ?>" <?= (int)($item['parent_id'] ?? 0) === (int)$q['id'] ? 'selected' : '' ?>><?= e(truncate($q['prompt'], 80)) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
    </div>
    <label class="field"><span>Notas</span><textarea name="notes" rows="3"><?= e($item['notes'] ?? '') ?></textarea></label>
    <button class="btn btn-accent" type="submit">Guardar</button>
</form>
</section>
