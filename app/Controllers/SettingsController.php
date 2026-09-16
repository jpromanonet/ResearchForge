<?php

declare(strict_types=1);

final class SettingsController
{
    public static function index(): void
    {
        Auth::requireLogin();
        refresh_auth_user();
        view('settings/index', [
            'title' => 'Configuración',
            'user' => UserService::find(Auth::id()) ?? Auth::user(),
        ]);
    }

    public static function save(): void
    {
        Auth::requireLogin();
        require_csrf();
        try {
            UserService::updateProfile(
                Auth::id(),
                (string) input('name', ''),
                (string) input('theme', 'system')
            );
            flash('success', 'Perfil actualizado.');
        } catch (Throwable $e) {
            flash('error', $e->getMessage());
        }
        redirect('/configuracion');
    }

    public static function password(): void
    {
        Auth::requireLogin();
        require_csrf();
        try {
            UserService::changePassword(
                Auth::id(),
                (string) input('current_password', ''),
                (string) input('new_password', ''),
                (string) input('new_password_confirm', '')
            );
            flash('success', 'Contraseña actualizada.');
        } catch (Throwable $e) {
            flash('error', $e->getMessage());
        }
        redirect('/configuracion');
    }

    public static function avatar(): void
    {
        Auth::requireLogin();
        require_csrf();
        try {
            UserService::uploadAvatar(Auth::id(), $_FILES['avatar'] ?? []);
            flash('success', 'Foto de perfil actualizada.');
        } catch (Throwable $e) {
            flash('error', $e->getMessage());
        }
        redirect('/configuracion');
    }

    public static function avatarRemove(): void
    {
        Auth::requireLogin();
        require_csrf();
        UserService::removeAvatar(Auth::id());
        flash('success', 'Foto de perfil eliminada.');
        redirect('/configuracion');
    }

    public static function theme(): void
    {
        Auth::requireLogin();
        require_csrf();
        $theme = (string) input('theme', 'light');
        Auth::setTheme($theme);
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            header('Content-Type: application/json');
            echo json_encode(['ok' => true, 'theme' => $theme]);
            exit;
        }
        redirect('/configuracion');
    }
}
