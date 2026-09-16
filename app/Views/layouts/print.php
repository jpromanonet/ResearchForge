<?php
/** @var string $templateFile */
/** @var string $appName */
/** @var string|null $title */
/** @var bool $autoPrint */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e(($title ?? 'Exportar') . ' · ' . $appName) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Newsreader:opsz,wght@6..72,500;6..72,700&family=Sora:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(url('/assets/css/app.css')) ?>?v=<?= (int) @filemtime(dirname(__DIR__, 3) . '/assets/css/app.css') ?>">
    <link rel="stylesheet" href="<?= e(url('/assets/css/print.css')) ?>?v=<?= (int) @filemtime(dirname(__DIR__, 3) . '/assets/css/print.css') ?>">
</head>
<body class="print-body">
<?php require $templateFile; ?>
<?php if (!empty($autoPrint)): ?>
<script>window.addEventListener('load', function () { setTimeout(function () { window.print(); }, 250); });</script>
<?php endif; ?>
</body>
</html>
