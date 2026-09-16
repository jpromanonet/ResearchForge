<?php

declare(strict_types=1);

final class UserService
{
    public static function find(int $id): ?array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT id, name, email, theme, avatar, created_at, last_login_at FROM users WHERE id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function updateProfile(int $id, string $name, string $theme): void
    {
        $name = trim($name);
        if ($name === '' || mb_strlen($name) < 2) {
            throw new InvalidArgumentException('Nombre inválido.');
        }
        if (!in_array($theme, ['light', 'dark', 'system'], true)) {
            $theme = 'system';
        }
        $stmt = Database::pdo()->prepare('UPDATE users SET name = :name, theme = :theme WHERE id = :id');
        $stmt->execute(['name' => $name, 'theme' => $theme, 'id' => $id]);
        $_SESSION['user']['name'] = $name;
        Auth::setTheme($theme);
    }

    public static function changePassword(int $id, string $current, string $new, string $confirm): void
    {
        if ($new !== $confirm) {
            throw new InvalidArgumentException('Las contraseñas nuevas no coinciden.');
        }
        if (strlen($new) < 8) {
            throw new InvalidArgumentException('La nueva contraseña debe tener al menos 8 caracteres.');
        }
        $stmt = Database::pdo()->prepare('SELECT password_hash FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        if (!$row || !password_verify($current, $row['password_hash'])) {
            throw new InvalidArgumentException('La contraseña actual no es correcta.');
        }
        $upd = Database::pdo()->prepare('UPDATE users SET password_hash = :hash WHERE id = :id');
        $upd->execute([
            'hash' => password_hash($new, PASSWORD_DEFAULT),
            'id' => $id,
        ]);
    }

    public static function uploadAvatar(int $id, array $file): string
    {
        $err = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($err === UPLOAD_ERR_NO_FILE) {
            throw new InvalidArgumentException('Elegí una imagen.');
        }
        if ($err === UPLOAD_ERR_INI_SIZE || $err === UPLOAD_ERR_FORM_SIZE) {
            throw new InvalidArgumentException('La imagen supera el límite permitido (máx. 2 MB).');
        }
        if ($err !== UPLOAD_ERR_OK) {
            throw new InvalidArgumentException('No se pudo subir la imagen (código ' . $err . ').');
        }
        if (($file['size'] ?? 0) > 2 * 1024 * 1024) {
            throw new InvalidArgumentException('La imagen no puede superar 2 MB.');
        }
        if (empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            throw new InvalidArgumentException('Archivo temporal inválido.');
        }

        $mime = '';
        if (class_exists('finfo')) {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = (string) ($finfo->file($file['tmp_name']) ?: '');
        }
        if ($mime === '' && function_exists('mime_content_type')) {
            $mime = (string) (mime_content_type($file['tmp_name']) ?: '');
        }
        if ($mime === '') {
            $imageInfo = @getimagesize($file['tmp_name']);
            $mime = is_array($imageInfo) ? (string) ($imageInfo['mime'] ?? '') : '';
        }

        $map = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
        ];
        if (!isset($map[$mime])) {
            throw new InvalidArgumentException('Formato inválido. Usá JPG, PNG, WEBP o GIF.');
        }

        $dir = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'avatars';
        if (!is_dir($dir)) {
            if (!@mkdir($dir, 0775, true) && !is_dir($dir)) {
                throw new RuntimeException('No se pudo crear storage/avatars.');
            }
        }
        if (!is_writable($dir)) {
            @chmod($dir, 0775);
        }
        if (!is_writable($dir)) {
            throw new RuntimeException('storage/avatars no tiene permiso de escritura en el servidor.');
        }

        $current = self::find($id);
        if ($current && !empty($current['avatar'])) {
            $old = $dir . DIRECTORY_SEPARATOR . basename((string) $current['avatar']);
            if (is_file($old)) {
                @unlink($old);
            }
        }

        $filename = 'u' . $id . '_' . bin2hex(random_bytes(6)) . '.' . $map[$mime];
        $dest = $dir . DIRECTORY_SEPARATOR . $filename;

        $moved = @move_uploaded_file($file['tmp_name'], $dest);
        if (!$moved) {
            $moved = @copy($file['tmp_name'], $dest);
            if ($moved) {
                @unlink($file['tmp_name']);
            }
        }
        if (!$moved || !is_file($dest)) {
            throw new RuntimeException('Error al guardar la foto. Revisá permisos de storage/avatars.');
        }
        @chmod($dest, 0644);

        $stmt = Database::pdo()->prepare('UPDATE users SET avatar = :avatar WHERE id = :id');
        $stmt->execute(['avatar' => $filename, 'id' => $id]);
        $_SESSION['user']['avatar'] = $filename;

        return $filename;
    }

    public static function removeAvatar(int $id): void
    {
        $current = self::find($id);
        if ($current && !empty($current['avatar'])) {
            $path = dirname(__DIR__, 2) . '/storage/avatars/' . basename((string) $current['avatar']);
            if (is_file($path)) {
                @unlink($path);
            }
        }
        $stmt = Database::pdo()->prepare('UPDATE users SET avatar = NULL WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $_SESSION['user']['avatar'] = null;
    }
}
