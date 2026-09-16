<section class="page-head">
    <div>
        <p class="eyebrow"><?= e($project['title']) ?></p>
        <h1 class="page-title"><?= e($item ? 'Editar cita' : 'Nueva cita') ?></h1>
    </div>
    <a class="btn" href="<?= e(url('/investigaciones/' . $project['id'] . '?tab=citas')) ?>">Volver</a>
</section>
<section class="panel">
<?php if (empty($sources)): ?>
    <p class="empty">Primero registrá una fuente.</p>
<?php else: ?>
<form class="stack-form" method="post" action="<?= e(url($item ? '/investigaciones/' . $project['id'] . '/citas/' . $item['id'] : '/investigaciones/' . $project['id'] . '/citas')) ?>">
    <?= csrf_field() ?>
    <label class="field"><span>Extracto</span><textarea name="excerpt" rows="5" required><?= e($item['excerpt'] ?? '') ?></textarea></label>
    <div class="form-grid-2">
        <label class="field"><span>Fuente</span>
            <select name="source_id" required>
                <?php foreach ($sources as $s): ?>
                    <option value="<?= (int)$s['id'] ?>" <?= (int)($item['source_id'] ?? 0) === (int)$s['id'] ? 'selected' : '' ?>><?= e($s['title']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label class="field"><span>Localizador (pág., timestamp…)</span><input type="text" name="locator" value="<?= e($item['locator'] ?? '') ?>"></label>
        <label class="field"><span>Afirmación (opcional)</span>
            <select name="claim_id">
                <option value="">—</option>
                <?php foreach ($claims as $c): ?>
                    <option value="<?= (int)$c['id'] ?>" <?= (int)($item['claim_id'] ?? 0) === (int)$c['id'] ? 'selected' : '' ?>><?= e(truncate($c['statement'], 80)) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
    </div>
    <label class="field"><span>Contexto</span><textarea name="context_note" rows="2"><?= e($item['context_note'] ?? '') ?></textarea></label>
    <label class="field"><span>Traducción</span><textarea name="translation" rows="3"><?= e($item['translation'] ?? '') ?></textarea></label>
    <button class="btn btn-accent" type="submit">Guardar</button>
</form>
<?php endif; ?>
</section>
