<?php

declare(strict_types=1);

final class Auth
{
    public static function startSession(string $name): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
        $lifetime = (int) app_config('session_lifetime', 86400);
        if ($lifetime < 300) {
            $lifetime = 300;
        }
        $idle = (int) app_config('session_idle', $lifetime);
        if ($idle < 60) {
            $idle = $lifetime;
        }

        ini_set('session.gc_maxlifetime', (string) max($lifetime, $idle));

        $cookiePath = self::sessionCookiePath();
        session_name($name);
        session_set_cookie_params([
            'lifetime' => $lifetime,
            'path' => $cookiePath,
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start([
            'cookie_lifetime' => $lifetime,
            'cookie_path' => $cookiePath,
            'cookie_httponly' => true,
            'cookie_samesite' => 'Lax',
            'cookie_secure' => $secure,
            'use_strict_mode' => true,
            'use_only_cookies' => true,
        ]);

        if (empty($_SESSION['_cookie_paths_cleaned'])) {
            self::expireStraySessionCookies($cookiePath, $secure);
            $_SESSION['_cookie_paths_cleaned'] = 1;
        }

        self::enforceIdleTimeout($idle, $lifetime);
    }

    public static function register(string $name, string $email, string $password): array
    {
        $name = trim($name);
        $email = strtolower(trim($email));

        if ($name === '' || mb_strlen($name) < 2) {
            throw new InvalidArgumentException('Ingresá un nombre válido.');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Ingresá un correo válido.');
        }
        if (strlen($password) < 8) {
            throw new InvalidArgumentException('La contraseña debe tener al menos 8 caracteres.');
        }

        $exists = Database::pdo()->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
        $exists->execute(['email' => $email]);
        if ($exists->fetch()) {
            throw new InvalidArgumentException('Ya hay una cuenta con ese correo.');
        }

        $stmt = Database::pdo()->prepare(
            'INSERT INTO users (name, email, password_hash) VALUES (:name, :email, :hash)'
        );
        $stmt->execute([
            'name' => $name,
            'email' => $email,
            'hash' => password_hash($password, PASSWORD_DEFAULT),
        ]);

        $id = (int) Database::pdo()->lastInsertId();
        return [
            'id' => $id,
            'name' => $name,
            'email' => $email,
            'theme' => 'system',
        ];
    }

    public static function attempt(string $email, string $password): bool
    {
        if (!self::allowLoginAttempt()) {
            return false;
        }

        $email = strtolower(trim($email));
        $stmt = Database::pdo()->prepare(
            'SELECT id, name, email, password_hash, theme, avatar, is_active
             FROM users WHERE email = :email LIMIT 1'
        );
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if (!$user || !(int) $user['is_active']) {
            self::recordLoginFailure();
            return false;
        }

        if (!password_verify($password, $user['password_hash'])) {
            self::recordLoginFailure();
            return false;
        }

        self::clearLoginFailures();
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id' => (int) $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'theme' => $user['theme'] ?: 'system',
            'avatar' => $user['avatar'] ?? null,
        ];
        $_SESSION['_last_activity'] = time();
        self::refreshSessionCookie((int) app_config('session_lifetime', 86400));

        $upd = Database::pdo()->prepare('UPDATE users SET last_login_at = NOW() WHERE id = :id');
        $upd->execute(['id' => $user['id']]);

        return true;
    }

    public static function loginUser(array $user): void
    {
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id' => (int) $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'theme' => $user['theme'] ?? 'system',
            'avatar' => $user['avatar'] ?? null,
        ];
        $_SESSION['_last_activity'] = time();
        self::refreshSessionCookie((int) app_config('session_lifetime', 86400));
    }

    public static function check(): bool
    {
        return isset($_SESSION['user']['id']);
    }

    public static function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    public static function id(): int
    {
        return (int) ($_SESSION['user']['id'] ?? 0);
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
            $path = self::sessionCookiePath();
            self::expireSessionCookie($path, $secure);
            self::expireStraySessionCookies($path, $secure);
        }
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            flash('error', 'Necesitás iniciar sesión.');
            redirect('/login');
        }
    }

    public static function theme(): string
    {
        $theme = $_SESSION['user']['theme'] ?? 'system';
        return in_array($theme, ['light', 'dark', 'system'], true) ? $theme : 'system';
    }

    public static function setTheme(string $theme): void
    {
        if (!in_array($theme, ['light', 'dark', 'system'], true)) {
            $theme = 'system';
        }
        if (self::check()) {
            $_SESSION['user']['theme'] = $theme;
            $stmt = Database::pdo()->prepare('UPDATE users SET theme = :theme WHERE id = :id');
            $stmt->execute(['theme' => $theme, 'id' => self::id()]);
        }
    }

    public static function isLoginLocked(): bool
    {
        $fails = $_SESSION['_login_fails'] ?? ['count' => 0, 'until' => 0];
        return ((int) ($fails['until'] ?? 0)) > time();
    }

    private static function enforceIdleTimeout(int $idleSeconds, int $cookieLifetime = 86400): void
    {
        if ($idleSeconds < 60 || !isset($_SESSION['user'])) {
            return;
        }
        $last = (int) ($_SESSION['_last_activity'] ?? 0);
        if ($last > 0 && (time() - $last) > $idleSeconds) {
            self::logout();
            return;
        }
        $_SESSION['_last_activity'] = time();
        self::refreshSessionCookie($cookieLifetime > 0 ? $cookieLifetime : $idleSeconds);
    }

    private static function sessionCookiePath(): string
    {
        $base = function_exists('base_path') ? base_path() : '';
        if ($base === '' || $base === '/') {
            return '/';
        }
        return $base;
    }

    private static function expireSessionCookie(string $path, bool $secure): void
    {
        setcookie(session_name(), '', [
            'expires' => time() - 42000,
            'path' => $path,
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    private static function expireStraySessionCookies(string $canonicalPath, bool $secure): void
    {
        if ($canonicalPath !== '/') {
            self::expireSessionCookie('/', $secure);
        }
    }

    private static function refreshSessionCookie(int $lifetime): void
    {
        if ($lifetime < 300 || session_status() !== PHP_SESSION_ACTIVE) {
            return;
        }
        $params = session_get_cookie_params();
        $path = $params['path'] !== '' ? $params['path'] : self::sessionCookiePath();
        $options = [
            'expires' => time() + $lifetime,
            'path' => $path,
            'secure' => (bool) ($params['secure'] ?? false),
            'httponly' => (bool) ($params['httponly'] ?? true),
            'samesite' => $params['samesite'] !== '' ? $params['samesite'] : 'Lax',
        ];
        if (($params['domain'] ?? '') !== '') {
            $options['domain'] = $params['domain'];
        }
        setcookie(session_name(), session_id(), $options);
    }

    private static function allowLoginAttempt(): bool
    {
        $fails = $_SESSION['_login_fails'] ?? ['count' => 0, 'until' => 0];
        return ((int) ($fails['until'] ?? 0)) <= time();
    }

    private static function recordLoginFailure(): void
    {
        $fails = $_SESSION['_login_fails'] ?? ['count' => 0, 'until' => 0];
        $fails['count'] = (int) $fails['count'] + 1;
        if ($fails['count'] >= 8) {
            $fails['until'] = time() + 300;
            $fails['count'] = 0;
        }
        $_SESSION['_login_fails'] = $fails;
    }

    private static function clearLoginFailures(): void
    {
        unset($_SESSION['_login_fails']);
    }
}
