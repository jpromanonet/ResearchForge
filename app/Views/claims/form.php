<section class="page-head">
    <div>
        <p class="eyebrow"><?= e($project['title']) ?></p>
        <h1 class="page-title"><?= e($item ? 'Editar afirmación' : 'Nueva afirmación') ?></h1>
    </div>
    <a class="btn" href="<?= e(url('/investigaciones/' . $project['id'] . '?tab=afirmaciones')) ?>">Volver</a>
</section>
<section class="panel">
<form class="stack-form" method="post" action="<?= e(url($item ? '/investigaciones/' . $project['id'] . '/afirmaciones/' . $item['id'] : '/investigaciones/' . $project['id'] . '/afirmaciones')) ?>">
    <?= csrf_field() ?>
    <label class="field"><span>Enunciado</span><textarea name="statement" rows="4" required><?= e($item['statement'] ?? '') ?></textarea></label>
    <div class="form-grid-2">
        <label class="field"><span>Tipo</span>
            <select name="claim_type"><?php foreach (claim_types() as $k => $l): ?><option value="<?= e($k) ?>" <?= ($item['claim_type'] ?? 'factual') === $k ? 'selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?></select>
        </label>
        <label class="field"><span>Estado</span>
            <select name="status"><?php foreach (claim_statuses() as $k => $l): ?><option value="<?= e($k) ?>" <?= ($item['status'] ?? 'hypothesis') === $k ? 'selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?></select>
        </label>
        <label class="field"><span>Confianza (0–100)</span><input type="number" min="0" max="100" name="confidence" value="<?= e((string)($item['confidence'] ?? '50')) ?>"></label>
        <label class="field"><span>Pregunta vinculada</span>
            <select name="question_id">
                <option value="">—</option>
                <?php foreach ($questions as $q): ?>
                    <option value="<?= (int)$q['id'] ?>" <?= (int)($item['question_id'] ?? 0) === (int)$q['id'] ? 'selected' : '' ?>><?= e(truncate($q['prompt'], 80)) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
    </div>
    <label class="field"><span>Notas</span><textarea name="notes" rows="3"><?= e($item['notes'] ?? '') ?></textarea></label>
    <button class="btn btn-accent" type="submit">Guardar</button>
</form>
</section>
