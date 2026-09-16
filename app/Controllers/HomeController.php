<?php

declare(strict_types=1);

final class HomeController
{
    public static function index(): void
    {
        if (Auth::check()) {
            redirect('/panel');
        }
        view('home/landing', [
            'title' => 'Forjá evidencia',
        ], 'layouts/landing');
    }
}
