<?php

declare(strict_types=1);

final class Schema
{
    public static function ensure(): void
    {
        $pdo = Database::pdo();
        $exists = $pdo->query("SHOW TABLES LIKE 'users'")->fetch();
        if (!$exists) {
            $sqlFile = dirname(__DIR__, 2) . '/sql/schema.sql';
            if (!is_file($sqlFile)) {
                throw new RuntimeException('Missing sql/schema.sql');
            }

            $sql = (string) file_get_contents($sqlFile);
            foreach (preg_split('/;\s*\n/', $sql) as $stmt) {
                $stmt = trim($stmt);
                if ($stmt === '' || str_starts_with($stmt, '--')) {
                    continue;
                }
                if (preg_match('/^(SET|CREATE DATABASE|USE)\b/i', $stmt)) {
                    continue;
                }
                $pdo->exec($stmt);
            }
        }

        self::migrate($pdo);
    }

    private static function migrate(PDO $pdo): void
    {
        $avatar = $pdo->query("SHOW COLUMNS FROM users LIKE 'avatar'")->fetch();
        if (!$avatar) {
            $pdo->exec('ALTER TABLE users ADD COLUMN avatar VARCHAR(255) NULL AFTER theme');
        }
    }
}
