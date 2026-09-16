<?php
$d = $assembled['dossier'];
$s = $assembled['sections'];
$pid = (int) $project['id'];
$did = (int) $d['id'];
?>
<section class="page-head">
    <div>
        <p class="eyebrow">Dossier</p>
        <h1 class="page-title"><?= e($d['title']) ?></h1>
        <?php if (!empty($d['subtitle'])): ?><p class="lede"><?= e($d['subtitle']) ?></p><?php endif; ?>
        <div class="chip-row">
            <span class="badge <?= e(status_badge_class($d['status'])) ?>"><?= e(dossier_statuses()[$d['status']] ?? $d['status']) ?></span>
            <span class="badge badge-copper"><?= e(dossier_modes()[$d['mode']] ?? $d['mode']) ?></span>
        </div>
    </div>
    <div class="page-actions">
        <a class="btn" href="<?= e(url('/investigaciones/' . $pid . '/dossiers/' . $did . '/editar')) ?>">Editar</a>
        <a class="btn btn-accent" href="<?= e(url('/investigaciones/' . $pid . '/dossiers/' . $did . '/exportar?format=md')) ?>">MD</a>
        <a class="btn" href="<?= e(url('/investigaciones/' . $pid . '/dossiers/' . $did . '/exportar?format=html')) ?>">HTML</a>
        <a class="btn" href="<?= e(url('/investigaciones/' . $pid . '/dossiers/' . $did . '/exportar?format=json')) ?>">JSON</a>
    </div>
</section>

<?php if (!empty($d['executive_summary'])): ?>
<section class="panel">
    <h2>Resumen ejecutivo</h2>
    <p><?= nl2br(e($d['executive_summary'])) ?></p>
</section>
<?php endif; ?>

<?php if ($s['questions']): ?>
<section class="panel"><h2>Preguntas</h2><ul class="plain-list"><?php foreach ($s['questions'] as $q): ?><li><?= e($q['prompt']) ?></li><?php endforeach; ?></ul></section>
<?php endif; ?>

<?php if ($s['sources']): ?>
<section class="panel"><h2>Fuentes</h2><ul class="plain-list"><?php foreach ($s['sources'] as $src): ?><li><strong><?= e($src['title']) ?></strong><?php if (!empty($src['authors'])): ?> — <?= e($src['authors']) ?><?php endif; ?></li><?php endforeach; ?></ul></section>
<?php endif; ?>

<?php if ($s['claims']): ?>
<section class="panel"><h2>Afirmaciones</h2><ul class="plain-list"><?php foreach ($s['claims'] as $c): ?><li><?= e($c['statement']) ?> <span class="badge <?= e(status_badge_class($c['status'])) ?>"><?= e(claim_statuses()[$c['status']] ?? '') ?></span></li><?php endforeach; ?></ul></section>
<?php endif; ?>

<?php if ($s['quotes']): ?>
<section class="panel"><h2>Citas</h2><?php foreach ($s['quotes'] as $q): ?><blockquote class="quote-block"><?= e($q['excerpt']) ?></blockquote><p class="muted">— <?= e($q['source_title'] ?? '') ?><?= !empty($q['locator']) ? ', ' . e($q['locator']) : '' ?></p><?php endforeach; ?></section>
<?php endif; ?>

<?php if ($s['evidence']): ?>
<section class="panel"><h2>Evidencias</h2><ul class="plain-list"><?php foreach ($s['evidence'] as $ev): ?><li><span class="badge <?= e(direction_badge_class($ev['direction'])) ?>"><?= e(evidence_directions()[$ev['direction']] ?? '') ?></span> <?= e($ev['summary']) ?></li><?php endforeach; ?></ul></section>
<?php endif; ?>

<?php if ($s['conclusions']): ?>
<section class="panel"><h2>Conclusiones</h2><?php foreach ($s['conclusions'] as $c): ?><article class="conclusion-block"><h3><?= e($c['title']) ?></h3><p><?= nl2br(e($c['body'])) ?></p></article><?php endforeach; ?></section>
<?php endif; ?>
