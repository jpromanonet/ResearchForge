<?php

declare(strict_types=1);

final class AuthController
{
    public static function showLogin(): void
    {
        if (Auth::check()) {
            redirect('/panel');
        }
        view('auth/login', ['title' => 'Iniciar sesión'], 'layouts/auth');
    }

    public static function login(): void
    {
        require_csrf();
        $email = trim((string) input('email', ''));
        $password = (string) input('password', '');

        if ($email === '' || $password === '') {
            flash('error', 'Ingresá tu correo y contraseña.');
            redirect('/login');
        }

        if (!Auth::attempt($email, $password)) {
            if (Auth::isLoginLocked()) {
                flash('error', 'Demasiados intentos. Esperá unos minutos.');
            } else {
                flash('error', 'Credenciales incorrectas o cuenta inactiva.');
            }
            redirect('/login');
        }

        flash('success', 'Bienvenido de nuevo.');
        redirect('/panel');
    }

    public static function showRegister(): void
    {
        if (Auth::check()) {
            redirect('/panel');
        }
        view('auth/register', ['title' => 'Crear cuenta'], 'layouts/auth');
    }

    public static function register(): void
    {
        require_csrf();
        try {
            $password = (string) input('password', '');
            $passwordConfirm = (string) input('password_confirm', '');
            if ($password !== $passwordConfirm) {
                throw new InvalidArgumentException('Las contraseñas no coinciden.');
            }
            $user = Auth::register(
                (string) input('name', ''),
                (string) input('email', ''),
                $password
            );
            Auth::loginUser($user);
            flash('success', 'Cuenta creada. Empezá tu primera investigación.');
            redirect('/panel');
        } catch (Throwable $e) {
            flash('error', $e->getMessage());
            redirect('/registro');
        }
    }

    public static function logout(): void
    {
        require_csrf();
        Auth::logout();
        flash('success', 'Sesión cerrada.');
        redirect('/login');
    }
}
