<?php

declare(strict_types=1);

final class DashboardController
{
    public static function index(): void
    {
        Auth::requireLogin();
        view('dashboard/index', [
            'title' => 'Panel',
            'stats' => StatsService::forUser(Auth::id()),
        ]);
    }
}
