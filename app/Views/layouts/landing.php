<?php
/** @var string $templateFile */
/** @var string $appName */
/** @var string|null $title */
?>
<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e(($title ?? '') !== '' ? $title . ' · ' . $appName : $appName) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Newsreader:opsz,wght@6..72,500;6..72,700&family=Sora:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(url('/assets/css/app.css')) ?>">
    <link rel="icon" href="<?= e(url('/assets/icons/forge.svg')) ?>" type="image/svg+xml">
</head>
<body class="landing-body">
<?php require $templateFile; ?>
<script src="<?= e(url('/assets/js/app.js')) ?>"></script>
</body>
</html>
