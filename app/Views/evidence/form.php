<section class="page-head">
    <div>
        <p class="eyebrow"><?= e($project['title']) ?></p>
        <h1 class="page-title"><?= e($item ? 'Editar evidencia' : 'Nueva evidencia') ?></h1>
    </div>
    <a class="btn" href="<?= e(url('/investigaciones/' . $project['id'] . '?tab=evidencias')) ?>">Volver</a>
</section>
<section class="panel">
<?php if (empty($claims)): ?>
    <p class="empty">Primero creá una afirmación.</p>
<?php else: ?>
<form class="stack-form" method="post" action="<?= e(url($item ? '/investigaciones/' . $project['id'] . '/evidencias/' . $item['id'] : '/investigaciones/' . $project['id'] . '/evidencias')) ?>">
    <?= csrf_field() ?>
    <label class="field"><span>Resumen</span><textarea name="summary" rows="4" required><?= e($item['summary'] ?? '') ?></textarea></label>
    <div class="form-grid-2">
        <label class="field"><span>Afirmación</span>
            <select name="claim_id" required>
                <?php foreach ($claims as $c): ?>
                    <option value="<?= (int)$c['id'] ?>" <?= (int)($item['claim_id'] ?? 0) === (int)$c['id'] ? 'selected' : '' ?>><?= e(truncate($c['statement'], 80)) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label class="field"><span>Dirección</span>
            <select name="direction"><?php foreach (evidence_directions() as $k => $l): ?><option value="<?= e($k) ?>" <?= ($item['direction'] ?? 'supports') === $k ? 'selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?></select>
        </label>
        <label class="field"><span>Fuerza</span>
            <select name="strength"><?php foreach (evidence_strengths() as $k => $l): ?><option value="<?= e($k) ?>" <?= ($item['strength'] ?? 'moderate') === $k ? 'selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?></select>
        </label>
        <label class="field"><span>Tipo</span>
            <select name="evidence_type">
                <?php foreach (['document'=>'Documento','data'=>'Dato','testimony'=>'Testimonio','experiment'=>'Experimento','observation'=>'Observación'] as $k=>$l): ?>
                    <option value="<?= e($k) ?>" <?= ($item['evidence_type'] ?? 'document') === $k ? 'selected' : '' ?>><?= e($l) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label class="field"><span>Fuente (opcional)</span>
            <select name="source_id">
                <option value="">—</option>
                <?php foreach ($sources as $s): ?>
                    <option value="<?= (int)$s['id'] ?>" <?= (int)($item['source_id'] ?? 0) === (int)$s['id'] ? 'selected' : '' ?>><?= e($s['title']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label class="field"><span>Cita (opcional)</span>
            <select name="quote_id">
                <option value="">—</option>
                <?php foreach ($quotes as $q): ?>
                    <option value="<?= (int)$q['id'] ?>" <?= (int)($item['quote_id'] ?? 0) === (int)$q['id'] ? 'selected' : '' ?>><?= e(truncate($q['excerpt'], 80)) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
    </div>
    <label class="field"><span>Notas</span><textarea name="notes" rows="3"><?= e($item['notes'] ?? '') ?></textarea></label>
    <button class="btn btn-accent" type="submit">Guardar</button>
</form>
<?php endif; ?>
</section>
