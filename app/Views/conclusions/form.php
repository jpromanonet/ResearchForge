<section class="page-head">
    <div>
        <p class="eyebrow"><?= e($project['title']) ?></p>
        <h1 class="page-title"><?= e($item ? 'Editar conclusión' : 'Nueva conclusión') ?></h1>
    </div>
    <a class="btn" href="<?= e(url('/investigaciones/' . $project['id'] . '?tab=conclusiones')) ?>">Volver</a>
</section>
<section class="panel">
<form class="stack-form" method="post" action="<?= e(url($item ? '/investigaciones/' . $project['id'] . '/conclusiones/' . $item['id'] : '/investigaciones/' . $project['id'] . '/conclusiones')) ?>">
    <?= csrf_field() ?>
    <label class="field"><span>Título</span><input type="text" name="title" required value="<?= e($item['title'] ?? '') ?>"></label>
    <label class="field"><span>Desarrollo</span><textarea name="body" rows="6" required><?= e($item['body'] ?? '') ?></textarea></label>
    <div class="form-grid-2">
        <label class="field"><span>Tipo</span>
            <select name="conclusion_type"><?php foreach (conclusion_types() as $k => $l): ?><option value="<?= e($k) ?>" <?= ($item['conclusion_type'] ?? 'provisional') === $k ? 'selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?></select>
        </label>
        <label class="field"><span>Pregunta</span>
            <select name="question_id">
                <option value="">—</option>
                <?php foreach ($questions as $q): ?>
                    <option value="<?= (int)$q['id'] ?>" <?= (int)($item['question_id'] ?? 0) === (int)$q['id'] ? 'selected' : '' ?>><?= e(truncate($q['prompt'], 80)) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
    </div>
    <label class="field"><span>Limitaciones</span><textarea name="limitations" rows="3"><?= e($item['limitations'] ?? '') ?></textarea></label>
    <label class="field"><span>Próximos pasos</span><textarea name="next_steps" rows="3"><?= e($item['next_steps'] ?? '') ?></textarea></label>
    <button class="btn btn-accent" type="submit">Guardar</button>
</form>
</section>
