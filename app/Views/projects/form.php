<?php /** @var array|null $project */ ?>
<section class="page-head">
    <div>
        <p class="eyebrow">Investigación</p>
        <h1 class="page-title"><?= e($project ? 'Editar' : 'Nueva investigación') ?></h1>
    </div>
    <a class="btn" href="<?= e(url($project ? '/investigaciones/' . $project['id'] : '/investigaciones')) ?>">Volver</a>
</section>

<section class="panel">
    <form method="post" action="<?= e(url($project ? '/investigaciones/' . $project['id'] : '/investigaciones')) ?>" class="stack-form">
        <?= csrf_field() ?>
        <label class="field">
            <span>Título</span>
            <input type="text" name="title" required value="<?= e($project['title'] ?? '') ?>" maxlength="220">
        </label>
        <label class="field">
            <span>Resumen</span>
            <textarea name="summary" rows="4"><?= e($project['summary'] ?? '') ?></textarea>
        </label>
        <div class="form-grid-2">
            <label class="field">
                <span>Estado</span>
                <select name="status">
                    <?php foreach (project_statuses() as $key => $label): ?>
                        <option value="<?= e($key) ?>" <?= ($project['status'] ?? 'active') === $key ? 'selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label class="field">
                <span>Disciplina</span>
                <input type="text" name="discipline" value="<?= e($project['discipline'] ?? '') ?>" placeholder="Periodismo, academia…">
            </label>
        </div>
        <label class="field">
            <span>Confidencialidad</span>
            <select name="confidentiality">
                <?php foreach (['private' => 'Privada', 'internal' => 'Interna', 'shareable' => 'Compartible'] as $key => $label): ?>
                    <option value="<?= e($key) ?>" <?= ($project['confidentiality'] ?? 'private') === $key ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <div class="form-actions">
            <button type="submit" class="btn btn-accent"><?= $project ? 'Guardar' : 'Crear' ?></button>
        </div>
    </form>
</section>
