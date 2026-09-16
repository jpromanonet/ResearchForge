<?php

declare(strict_types=1);

/**
 * SVG charts for printable / PDF reports (no JS required).
 */
final class ChartSvg
{
    public static function bar(array $items, int $width = 520, int $height = 220): string
    {
        $items = array_values(array_filter($items, static fn ($i) => (int) ($i['value'] ?? 0) > 0 || true));
        if (!$items) {
            return self::emptyBox($width, $height);
        }
        $max = max(1, max(array_map(static fn ($i) => (int) $i['value'], $items)));
        $padL = 28;
        $padB = 48;
        $padT = 16;
        $padR = 12;
        $plotW = $width - $padL - $padR;
        $plotH = $height - $padT - $padB;
        $n = count($items);
        $gap = 8;
        $barW = max(8, ($plotW - $gap * ($n - 1)) / $n);
        $colors = self::colors();
        $svg = self::open($width, $height);
        for ($g = 0; $g <= 4; $g++) {
            $y = $padT + ($plotH * $g) / 4;
            $svg .= '<line x1="' . $padL . '" y1="' . $y . '" x2="' . ($width - $padR) . '" y2="' . $y . '" stroke="#C9C2B5" stroke-width="1"/>';
        }
        foreach ($items as $i => $item) {
            $v = (int) $item['value'];
            $bh = ($v / $max) * $plotH;
            $x = $padL + $i * ($barW + $gap);
            $y = $padT + $plotH - $bh;
            $color = $colors[$i % count($colors)];
            $svg .= '<rect x="' . $x . '" y="' . $y . '" width="' . $barW . '" height="' . max(1, $bh) . '" fill="' . $color . '"/>';
            $svg .= '<text x="' . ($x + $barW / 2) . '" y="' . max(12, $y - 4) . '" text-anchor="middle" font-size="10" fill="#5A6570">' . $v . '</text>';
            $label = self::short((string) ($item['label'] ?? ''), 10);
            $svg .= '<text x="' . ($x + $barW / 2) . '" y="' . ($height - 10) . '" text-anchor="middle" font-size="9" fill="#5A6570">' . e($label) . '</text>';
        }
        return $svg . '</svg>';
    }

    public static function pie(array $items, int $size = 220, bool $donut = false): string
    {
        $items = array_values(array_filter($items, static fn ($i) => (int) ($i['value'] ?? 0) > 0));
        if (!$items) {
            return self::emptyBox($size, $size);
        }
        $total = array_sum(array_map(static fn ($i) => (int) $i['value'], $items));
        $cx = $size / 2;
        $cy = $size / 2 - 8;
        $r = $size * 0.32;
        $colors = self::colors();
        $svg = self::open($size, $size);
        $angle = -M_PI / 2;
        foreach ($items as $i => $item) {
            $slice = ((int) $item['value'] / $total) * M_PI * 2;
            $x1 = $cx + cos($angle) * $r;
            $y1 = $cy + sin($angle) * $r;
            $angle2 = $angle + $slice;
            $x2 = $cx + cos($angle2) * $r;
            $y2 = $cy + sin($angle2) * $r;
            $large = $slice > M_PI ? 1 : 0;
            $color = $colors[$i % count($colors)];
            $svg .= '<path d="M' . $cx . ',' . $cy . ' L' . $x1 . ',' . $y1 . ' A' . $r . ',' . $r . ' 0 ' . $large . ' 1 ' . $x2 . ',' . $y2 . ' Z" fill="' . $color . '"/>';
            $angle = $angle2;
        }
        if ($donut) {
            $svg .= '<circle cx="' . $cx . '" cy="' . $cy . '" r="' . ($r * 0.55) . '" fill="#FAF7F1"/>';
            $svg .= '<text x="' . $cx . '" y="' . ($cy + 4) . '" text-anchor="middle" font-size="14" font-weight="700" fill="#1A2332">' . $total . '</text>';
        }
        $ly = $size - 6;
        $lx = 8;
        foreach ($items as $i => $item) {
            $color = $colors[$i % count($colors)];
            $label = self::short((string) $item['label'], 12) . ' (' . (int) $item['value'] . ')';
            $svg .= '<rect x="' . $lx . '" y="' . ($ly - 8) . '" width="8" height="8" fill="' . $color . '"/>';
            $svg .= '<text x="' . ($lx + 12) . '" y="' . $ly . '" font-size="9" fill="#5A6570">' . e($label) . '</text>';
            $lx += 90;
            if ($lx > $size - 80) {
                $lx = 8;
                $ly -= 12;
            }
        }
        return $svg . '</svg>';
    }

    public static function hbar(array $items, int $width = 520, int $height = 200): string
    {
        $items = array_values($items);
        if (!$items) {
            return self::emptyBox($width, $height);
        }
        $max = max(1, max(array_map(static fn ($i) => (int) $i['value'], $items)));
        $colors = self::colors();
        $padL = 110;
        $rowH = max(22, ($height - 20) / max(1, count($items)));
        $svg = self::open($width, $height);
        foreach ($items as $i => $item) {
            $y = 10 + $i * $rowH;
            $bw = (($width - $padL - 40) * (int) $item['value']) / $max;
            $svg .= '<text x="' . ($padL - 8) . '" y="' . ($y + 12) . '" text-anchor="end" font-size="10" fill="#5A6570">' . e(self::short((string) $item['label'], 14)) . '</text>';
            $svg .= '<rect x="' . $padL . '" y="' . $y . '" width="' . max(2, $bw) . '" height="14" fill="' . $colors[$i % count($colors)] . '"/>';
            $svg .= '<text x="' . ($padL + $bw + 6) . '" y="' . ($y + 11) . '" font-size="10" fill="#1A2332">' . (int) $item['value'] . '</text>';
        }
        return $svg . '</svg>';
    }

    public static function line(array $timeline, int $width = 720, int $height = 220): string
    {
        if (!$timeline) {
            return self::emptyBox($width, $height);
        }
        $pad = ['t' => 20, 'r' => 16, 'b' => 36, 'l' => 32];
        $max = 1;
        foreach ($timeline as $d) {
            $max = max($max, (int) ($d['total'] ?? 0), (int) ($d['claims'] ?? 0), (int) ($d['sources'] ?? 0));
        }
        $plotW = $width - $pad['l'] - $pad['r'];
        $plotH = $height - $pad['t'] - $pad['b'];
        $n = count($timeline);
        $svg = self::open($width, $height);
        for ($g = 0; $g <= 4; $g++) {
            $y = $pad['t'] + ($plotH * $g) / 4;
            $svg .= '<line x1="' . $pad['l'] . '" y1="' . $y . '" x2="' . ($width - $pad['r']) . '" y2="' . $y . '" stroke="#C9C2B5"/>';
        }
        $series = [
            'total' => '#B86B3A',
            'claims' => '#2F6B5A',
            'sources' => '#3A5F8A',
        ];
        foreach ($series as $key => $color) {
            $points = [];
            foreach ($timeline as $i => $d) {
                $x = $pad['l'] + ($n <= 1 ? 0 : ($plotW * $i) / ($n - 1));
                $y = $pad['t'] + $plotH - (((int) ($d[$key] ?? 0)) / $max) * $plotH;
                $points[] = $x . ',' . $y;
            }
            $svg .= '<polyline fill="none" stroke="' . $color . '" stroke-width="2" points="' . implode(' ', $points) . '"/>';
        }
        foreach ($timeline as $i => $d) {
            if ($i % 2 !== 0 && $i !== $n - 1) {
                continue;
            }
            $x = $pad['l'] + ($n <= 1 ? 0 : ($plotW * $i) / ($n - 1));
            $svg .= '<text x="' . $x . '" y="' . ($height - 12) . '" text-anchor="middle" font-size="9" fill="#5A6570">' . e((string) ($d['label'] ?? '')) . '</text>';
        }
        return $svg . '</svg>';
    }

    private static function colors(): array
    {
        return ['#B86B3A', '#2F6B5A', '#3A5F8A', '#9B3A3A', '#A67C2D', '#6B7C8A', '#8B5E73'];
    }

    private static function open(int $w, int $h): string
    {
        return '<svg xmlns="http://www.w3.org/2000/svg" width="' . $w . '" height="' . $h . '" viewBox="0 0 ' . $w . ' ' . $h . '" class="chart-svg">';
    }

    private static function emptyBox(int $w, int $h): string
    {
        return self::open($w, $h)
            . '<text x="' . ($w / 2) . '" y="' . ($h / 2) . '" text-anchor="middle" fill="#5A6570" font-size="12">Sin datos</text></svg>';
    }

    private static function short(string $text, int $len): string
    {
        if (mb_strlen($text) <= $len) {
            return $text;
        }
        return mb_substr($text, 0, $len - 1) . '…';
    }
}
