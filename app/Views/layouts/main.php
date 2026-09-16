<?php
/** @var string $templateFile */
/** @var string $appName */
/** @var string $appVersion */
/** @var string $title */
/** @var array|null $user */
$flashes = take_flashes();
$themePref = Auth::check() ? Auth::theme() : 'system';
$htmlTheme = $themePref === 'dark' ? 'dark' : 'light';
$projectCount = 0;
if (Auth::check()) {
    if (!array_key_exists('avatar', $_SESSION['user'] ?? [])) {
        refresh_auth_user();
        $user = Auth::user();
    }
    try {
        $stmt = Database::pdo()->prepare('SELECT COUNT(*) FROM projects WHERE user_id = :uid');
        $stmt->execute(['uid' => Auth::id()]);
        $projectCount = (int) $stmt->fetchColumn();
    } catch (Throwable $e) {
        $projectCount = 0;
    }
}
$userAvatar = Auth::check() ? avatar_url($user['avatar'] ?? null) : null;
$userName = Auth::check() ? (string) ($user['name'] ?? '') : '';
?>
<!DOCTYPE html>
<html lang="es" data-theme="<?= e($htmlTheme) ?>" data-theme-pref="<?= e($themePref) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e(($title ?? '') !== '' ? $title . ' · ' . $appName : $appName) ?></title>
    <script>
    (function () {
      try {
        var root = document.documentElement;
        var pref = localStorage.getItem('rf-theme') || root.getAttribute('data-theme-pref') || 'system';
        var theme = pref;
        if (pref === 'system') {
          theme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        }
        if (theme !== 'dark' && theme !== 'light') theme = 'light';
        root.setAttribute('data-theme', theme);
        root.setAttribute('data-theme-pref', pref);
      } catch (e) {}
    })();
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;600&family=Newsreader:opsz,wght@6..72,500;6..72,650;6..72,700&family=Sora:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(url('/assets/css/app.css')) ?>?v=<?= (int) @filemtime(dirname(__DIR__, 3) . '/assets/css/app.css') ?>">
    <link rel="icon" href="<?= e(url('/assets/icons/forge.svg')) ?>" type="image/svg+xml">
</head>
<body>
<div class="app-shell">
    <header class="topbar">
        <div class="topbar-brand-zone">
            <button type="button" class="sidebar-toggle" id="sidebar-toggle" aria-label="Menú" aria-expanded="false"><?= icon('menu', 18) ?></button>
            <a class="topbar-brand brand" href="<?= e(url('/panel')) ?>">
                <?= icon('forge', 22) ?>
                <span class="brand-text">ResearchForge</span>
            </a>
        </div>
        <div class="topbar-main">
            <div class="topbar-search">
                <form class="global-search" action="<?= e(url('/buscar')) ?>" method="get">
                    <input type="search" name="q" id="global-search" placeholder="Buscar preguntas, fuentes, claims…" value="<?= e($_GET['q'] ?? '') ?>" autocomplete="off">
                </form>
            </div>
            <div class="topbar-actions">
                <button
                    type="button"
                    class="theme-toggle"
                    data-theme-toggle
                    data-theme-url="<?= e(url('/configuracion/tema')) ?>"
                    data-csrf="<?= e(csrf_token()) ?>"
                    aria-label="Cambiar tema"
                    title="Cambiar tema"
                >
                    <span class="theme-icon-sun"><?= icon('sun', 16) ?></span>
                    <span class="theme-icon-moon"><?= icon('moon', 16) ?></span>
                </button>
                <span class="meta-pill"><?= format_number($projectCount) ?> investigaciones</span>
                <a class="btn btn-accent btn-sm" href="<?= e(url('/investigaciones/nueva')) ?>"><?= icon('add', 14) ?> Nueva</a>
                <a class="topbar-profile" href="<?= e(url('/configuracion')) ?>" title="Mi perfil">
                    <span class="avatar-preview avatar-sm">
                        <?php if ($userAvatar): ?>
                            <img src="<?= e($userAvatar) ?>" alt="">
                        <?php else: ?>
                            <span><?= e(user_initials($userName)) ?></span>
                        <?php endif; ?>
                    </span>
                    <span class="topbar-profile-name"><?= e($userName !== '' ? explode(' ', $userName)[0] : 'Perfil') ?></span>
                </a>
            </div>
        </div>
    </header>

    <aside class="sidebar" id="sidebar">
        <p class="sidebar-label"><?= e($user['name'] ?? 'Investigador') ?></p>
        <nav class="sidebar-nav">
            <a class="<?= e(nav_active('/panel', true)) ?>" href="<?= e(url('/panel')) ?>"><?= icon('dashboard') ?> Panel</a>
            <a class="<?= e(nav_active('/investigaciones')) ?>" href="<?= e(url('/investigaciones')) ?>"><?= icon('project') ?> Investigaciones</a>
            <a class="<?= e(nav_active('/buscar')) ?>" href="<?= e(url('/buscar')) ?>"><?= icon('search') ?> Buscar</a>
            <a class="<?= e(nav_active('/configuracion')) ?>" href="<?= e(url('/configuracion')) ?>"><?= icon('settings') ?> Configuración</a>
        </nav>
        <form method="post" action="<?= e(url('/logout')) ?>" class="sidebar-logout">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-ghost btn-block"><?= icon('logout', 14) ?> Salir</button>
        </form>
    </aside>
    <div class="sidebar-backdrop" aria-hidden="true"></div>

    <main class="content">
        <?php if ($flashes): ?>
            <div class="flash-stack">
                <?php foreach ($flashes as $flash): ?>
                    <div class="flash flash-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <?php require $templateFile; ?>
    </main>

    <footer class="statusbar">
        <span>ResearchForge v<?= e($appVersion) ?></span>
        <span>Preguntas · Fuentes · Evidencia · Dossiers</span>
        <span><?= e(strtoupper((string) ($title ?? 'FORGE'))) ?></span>
    </footer>
</div>
<script src="<?= e(url('/assets/js/app.js')) ?>?v=<?= (int) @filemtime(dirname(__DIR__, 3) . '/assets/js/app.js') ?>"></script>
</body>
</html>
