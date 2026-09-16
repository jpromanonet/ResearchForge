<?php

declare(strict_types=1);

function app_config(?string $key = null, mixed $default = null): mixed
{
    static $config = null;
    if ($config === null) {
        $config = require dirname(__DIR__) . '/config/app.php';
    }
    if ($key === null) {
        return $config;
    }
    return $config[$key] ?? $default;
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function base_path(): string
{
    static $cached = null;
    if ($cached !== null) {
        return $cached;
    }

    $appUrl = (string) app_config('url', '');
    if ($appUrl !== '') {
        $urlPath = parse_url($appUrl, PHP_URL_PATH);
        if (is_string($urlPath) && $urlPath !== '' && $urlPath !== '/') {
            $cached = rtrim($urlPath, '/');
            return $cached;
        }
        $cached = '';
        return $cached;
    }

    $script = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    if ($script === '/' || $script === '\\' || $script === '.') {
        $cached = '';
        return $cached;
    }
    $cached = rtrim($script, '/');
    return $cached;
}

function url(string $path = '/'): string
{
    $extraQuery = [];
    $hash = '';
    if (str_contains($path, '#')) {
        [$path, $hash] = explode('#', $path, 2);
        $hash = '#' . $hash;
    }
    if (str_contains($path, '?')) {
        [$path, $qs] = explode('?', $path, 2);
        parse_str($qs, $extraQuery);
    }

    $path = '/' . ltrim($path, '/');
    if ($path === '//') {
        $path = '/';
    }

    $base = base_path();

    if (str_starts_with($path, '/assets/') || str_starts_with($path, '/storage/') || preg_match('#^/[^/]+\.php$#', $path) === 1) {
        $suffix = $extraQuery ? ('?' . http_build_query($extraQuery)) : '';
        return $base . $path . $suffix . $hash;
    }

    $script = $base . '/index.php';
    $params = $extraQuery;
    if ($path !== '/') {
        $params = array_merge(['r' => $path], $params);
    }
    $suffix = $params ? ('?' . http_build_query($params)) : '';
    return $script . $suffix . $hash;
}

function redirect(string $path): never
{
    header('Location: ' . url($path));
    exit;
}

function view(string $template, array $vars = [], ?string $layout = 'layouts/main'): void
{
    extract($vars, EXTR_SKIP);
    $appName = (string) app_config('name', 'ResearchForge');
    $appVersion = (string) app_config('version', '0.1.0');
    $user = Auth::check() ? Auth::user() : null;
    $templateFile = dirname(__DIR__) . '/app/Views/' . $template . '.php';
    if (!is_file($templateFile)) {
        throw new RuntimeException('View not found: ' . $template);
    }
    if ($layout === null) {
        require $templateFile;
        return;
    }
    $layoutFile = dirname(__DIR__) . '/app/Views/' . $layout . '.php';
    if (!is_file($layoutFile)) {
        throw new RuntimeException('Layout not found: ' . $layout);
    }
    require $layoutFile;
}

function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function verify_csrf(?string $token = null): bool
{
    $token = $token ?? ($_POST['_csrf'] ?? null);
    return is_string($token)
        && isset($_SESSION['_csrf'])
        && hash_equals($_SESSION['_csrf'], $token);
}

function require_csrf(): void
{
    if (!verify_csrf($_POST['_csrf'] ?? null)) {
        http_response_code(419);
        flash('error', 'Sesión desfasada. Recargá e intentá de nuevo.');
        redirect(Auth::check() ? '/panel' : '/login');
    }
}

function flash(string $type, string $message): void
{
    $_SESSION['_flash'][] = ['type' => $type, 'message' => $message];
}

function take_flashes(): array
{
    $flashes = $_SESSION['_flash'] ?? [];
    unset($_SESSION['_flash']);
    return is_array($flashes) ? $flashes : [];
}

function nav_active(string $prefix, bool $exact = false): string
{
    $current = $_GET['r'] ?? '/';
    if ($current === '' || $current === false) {
        $current = '/';
    }
    $current = '/' . trim((string) $current, '/');
    if ($current === '//') {
        $current = '/';
    }
    if ($exact) {
        return $current === $prefix ? 'is-active' : '';
    }
    if ($prefix === '/') {
        return $current === '/' ? 'is-active' : '';
    }
    return str_starts_with($current, $prefix) ? 'is-active' : '';
}

function icon(string $name, int $size = 16): string
{
    $src = url('/assets/icons/' . $name . '.svg');
    return '<img class="px-icon" src="' . e($src) . '" width="' . $size . '" height="' . $size . '" alt="" aria-hidden="true">';
}

function format_number(int|float|null $n): string
{
    if ($n === null) {
        return '—';
    }
    return number_format((float) $n, 0, ',', '.');
}

function null_if_blank(?string $value): ?string
{
    if ($value === null) {
        return null;
    }
    $value = trim($value);
    return $value === '' ? null : $value;
}

function int_or_null(mixed $value): ?int
{
    if ($value === null || $value === '') {
        return null;
    }
    return (int) $value;
}

function input(string $key, mixed $default = null): mixed
{
    return $_POST[$key] ?? $_GET[$key] ?? $default;
}

function truncate(?string $text, int $len = 140): string
{
    $text = trim((string) $text);
    if ($text === '') {
        return '';
    }
    if (mb_strlen($text) <= $len) {
        return $text;
    }
    return rtrim(mb_substr($text, 0, $len - 1)) . '…';
}

function project_statuses(): array
{
    return [
        'draft' => 'Borrador',
        'active' => 'Activa',
        'paused' => 'Pausada',
        'closed' => 'Cerrada',
        'archived' => 'Archivada',
    ];
}

function project_status_label(string $status): string
{
    return project_statuses()[$status] ?? $status;
}

function question_statuses(): array
{
    return [
        'open' => 'Sin abordar',
        'in_progress' => 'En curso',
        'answered' => 'Respondida',
        'discarded' => 'Descartada',
    ];
}

function question_kinds(): array
{
    return [
        'main' => 'Principal',
        'secondary' => 'Secundaria',
        'open' => 'Abierta',
        'yesno' => 'Sí / No',
        'comparative' => 'Comparativa',
    ];
}

function source_types(): array
{
    return [
        'book' => 'Libro',
        'paper' => 'Paper',
        'article' => 'Artículo',
        'web' => 'Web',
        'pdf' => 'PDF',
        'video' => 'Video',
        'podcast' => 'Podcast',
        'interview' => 'Entrevista',
        'dataset' => 'Dataset',
        'law' => 'Normativa',
        'other' => 'Otro',
    ];
}

function reading_statuses(): array
{
    return [
        'to_read' => 'Por leer',
        'reading' => 'Leyendo',
        'read' => 'Leída',
        'discarded' => 'Descartada',
    ];
}

function claim_types(): array
{
    return [
        'factual' => 'Factual',
        'causal' => 'Causal',
        'normative' => 'Normativa',
        'predictive' => 'Predictiva',
        'interpretive' => 'Interpretativa',
    ];
}

function claim_statuses(): array
{
    return [
        'hypothesis' => 'Hipótesis',
        'proposed' => 'Propuesta',
        'supported' => 'Respaldada',
        'refuted' => 'Refutada',
        'disputed' => 'En disputa',
        'withdrawn' => 'Retirada',
    ];
}

function evidence_directions(): array
{
    return [
        'supports' => 'A favor',
        'against' => 'En contra',
        'contextual' => 'Contextual',
        'methodological' => 'Metodológica',
    ];
}

function evidence_strengths(): array
{
    return [
        'weak' => 'Débil',
        'moderate' => 'Moderada',
        'strong' => 'Fuerte',
    ];
}

function conclusion_types(): array
{
    return [
        'provisional' => 'Provisional',
        'firm' => 'Firme',
        'insufficient' => 'Evidencia insuficiente',
    ];
}

function dossier_modes(): array
{
    return [
        'briefing' => 'Briefing',
        'executive' => 'Ejecutivo',
        'academic' => 'Académico',
        'factcheck' => 'Fact-check',
    ];
}

function dossier_statuses(): array
{
    return [
        'draft' => 'Borrador',
        'review' => 'En revisión',
        'published' => 'Publicado',
        'frozen' => 'Congelado',
    ];
}

function status_badge_class(string $status): string
{
    return match ($status) {
        'active', 'answered', 'supported', 'read', 'published', 'firm' => 'badge-moss',
        'in_progress', 'reading', 'proposed', 'review', 'provisional' => 'badge-copper',
        'paused', 'open', 'to_read', 'hypothesis', 'draft', 'contextual' => 'badge-slate',
        'closed', 'discarded', 'refuted', 'against', 'withdrawn', 'archived' => 'badge-crimson',
        'disputed', 'insufficient' => 'badge-amber',
        default => 'badge-ink',
    };
}

function direction_badge_class(string $direction): string
{
    return match ($direction) {
        'supports' => 'badge-moss',
        'against' => 'badge-crimson',
        'contextual' => 'badge-source',
        'methodological' => 'badge-copper',
        default => 'badge-slate',
    };
}

function format_dt(?string $dt): string
{
    if ($dt === null || $dt === '') {
        return '—';
    }
    $ts = strtotime($dt);
    if ($ts === false) {
        return $dt;
    }
    return date('d/m/Y H:i', $ts);
}

function avatar_url(?string $avatar): ?string
{
    if ($avatar === null || $avatar === '') {
        return null;
    }
    return url('/storage/avatars/' . ltrim($avatar, '/'));
}

function user_initials(?string $name): string
{
    $name = trim((string) $name);
    if ($name === '') {
        return '?';
    }
    $parts = preg_split('/\s+/', $name) ?: [];
    $initials = '';
    foreach (array_slice($parts, 0, 2) as $part) {
        $initials .= mb_strtoupper(mb_substr($part, 0, 1));
    }
    return $initials !== '' ? $initials : '?';
}

function refresh_auth_user(): void
{
    if (!Auth::check()) {
        return;
    }
    $row = UserService::find(Auth::id());
    if (!$row) {
        return;
    }
    $_SESSION['user']['name'] = $row['name'];
    $_SESSION['user']['email'] = $row['email'];
    $_SESSION['user']['theme'] = $row['theme'] ?: 'system';
    $_SESSION['user']['avatar'] = $row['avatar'] ?? null;
}
