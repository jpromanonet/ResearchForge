<?php

declare(strict_types=1);

final class SearchController
{
    public static function index(): void
    {
        Auth::requireLogin();
        $q = trim((string) ($_GET['q'] ?? ''));
        view('search/index', [
            'title' => 'Buscar',
            'q' => $q,
            'results' => $q !== '' ? StatsService::search(Auth::id(), $q) : null,
        ]);
    }
}
