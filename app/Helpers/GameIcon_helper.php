<?php

if (! function_exists('game_icon_map')) {
    /** Эмодзи → PNG из /img/misc/ (единый вид иконок в БД и на фронте). */
    function game_icon_map(): array
    {
        return [
            '⚔️' => '/img/misc/fight.png',
            '🗡️' => '/img/misc/fight.png',
            '💀' => '/img/misc/fight.png',
            '🐗' => '/img/misc/fight.png',
            '🎒' => '/img/misc/bag.png',
            '👣' => '/img/misc/map.png',
            '🚶' => '/img/misc/map.png',
            '🗺️' => '/img/misc/map.png',
            '🧭' => '/img/misc/map.png',
            '🏰' => '/img/misc/town.png',
            '🏆' => '/img/misc/quests.png',
            '🥇' => '/img/misc/quests.png',
            '🗝️' => '/img/misc/chest.png',
            '🎁' => '/img/misc/chest.png',
            '💎' => '/img/misc/emerald.png',
            '💰' => '/img/misc/coin.png',
            '❤️' => '/img/misc/Heart.png',
            '⚡' => '/img/misc/energy.png',
        ];
    }
}

if (! function_exists('game_icon_is_path')) {
    /** Значение является путём/URL картинки, а не одиночным эмодзи? */
    function game_icon_is_path(string $icon): bool
    {
        $icon = trim($icon);
        if ($icon === '') {
            return false;
        }
        if (str_starts_with($icon, '/') || str_starts_with($icon, './')) {
            return true;
        }
        if (preg_match('#^https?://#i', $icon)) {
            return true;
        }

        return (bool) preg_match('/\.(png|svg|webp|ico|gif|jpe?g)$/i', $icon);
    }
}

if (! function_exists('game_icon_public_root')) {
    /** Абсолютный путь к каталогу public/ (для проверки существования файлов иконок). */
    function game_icon_public_root(): string
    {
        $root = defined('FCPATH') ? FCPATH : dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR;

        return rtrim(str_replace('\\', '/', $root), '/');
    }
}

if (! function_exists('game_icon_normalize_path')) {
    /**
     * Приведение иконки-пути к реально существующему файлу в public/.
     * Исправляет обрезанные значения из БД (/img/monsters/demon., /img/monsters/wolf.p, /img/monsters/skelet)
     * подбором файла по имени/префиксу. Возвращает '' если файл не найден.
     */
    function game_icon_normalize_path(string $icon): string
    {
        $icon = trim($icon);
        if ($icon === '' || ! (str_starts_with($icon, '/') || str_starts_with($icon, './'))) {
            return $icon;
        }
        if (str_starts_with($icon, '//')) {
            return $icon; // протокол-относительный URL — не трогаем
        }

        $relative = ltrim(str_replace('\\', '/', $icon), './');
        if ($relative === '') {
            return '';
        }

        $root = game_icon_public_root();
        if (is_file($root . '/' . $relative)) {
            return '/' . $relative;
        }

        $dir     = trim(dirname($relative), '.');
        $dirPath = $dir === '' ? '' : $dir . '/';
        $base    = rtrim(basename($relative), '.');
        if ($base === '') {
            return '';
        }

        // Список файлов каталога кэшируем на время запроса
        static $cache = [];
        if (! isset($cache[$dirPath])) {
            $files = [];
            foreach (glob($root . '/' . $dirPath . '*.{png,svg,webp,gif,jpg,jpeg}', GLOB_BRACE) ?: [] as $file) {
                $files[] = basename($file);
            }
            sort($files);
            $cache[$dirPath] = $files;
        }
        if ($cache[$dirPath] === []) {
            return '';
        }

        // Кандидаты от самого длинного к короткому: 'wolf.p' → 'wolf.p', 'wolf'
        $candidates = [$base];
        if (($dot = strpos($base, '.')) !== false) {
            $candidates[] = substr($base, 0, $dot);
        }
        $trimmed = $base;
        while (mb_strlen($trimmed) > 3) {
            $trimmed      = mb_substr($trimmed, 0, -1);
            $candidates[] = $trimmed;
        }

        foreach ($candidates as $candidate) {
            if (in_array($candidate . '.png', $cache[$dirPath], true)) {
                return '/' . $dirPath . $candidate . '.png';
            }
            foreach ($cache[$dirPath] as $file) {
                if (str_starts_with($file, $candidate)) {
                    return '/' . $dirPath . $file;
                }
            }
        }

        return '';
    }
}

if (! function_exists('game_icon_src')) {
    /** Путь к картинке для иконки: сам путь, затем иконка по типу предмета, затем маппинг эмодзи → PNG. Иначе ''. */
    function game_icon_src(string $icon, string $type = ''): string
    {
        $icon = trim($icon);

        // Абсолютные URL отдаём как есть
        if ($icon !== '' && preg_match('#^https?://#i', $icon)) {
            return $icon;
        }

        // Путь к файлу: приводим к реально существующему (обрезанные пути из старых БД)
        if ($icon !== '' && (str_starts_with($icon, '/') || str_starts_with($icon, './'))) {
            $resolved = game_icon_normalize_path($icon);
            if ($resolved !== '') {
                return $resolved;
            }
            // Путь битый — дальше работаем только с именем файла
            $icon = basename(str_replace('\\', '/', $icon));
        } elseif ($icon !== '' && preg_match('/\.(png|svg|webp|ico|gif|jpe?g)$/i', $icon)) {
            // Путь без ведущего слеша ('img/monsters/demon.png') или голое имя файла ('demon.png')
            $relative = ltrim(str_replace('\\', '/', $icon), './');
            $resolved = game_icon_normalize_path('/' . $relative);
            if ($resolved !== '') {
                return $resolved;
            }
            $name = basename($relative);
            foreach (['/img/monsters/', '/img/weapons/', '/img/shields/', '/img/misc/', '/img/ores/', '/img/tools/', '/img/chests/'] as $dir) {
                $resolved = game_icon_normalize_path($dir . $name);
                if ($resolved !== '') {
                    return $resolved;
                }
            }
            $icon = $name;
        }
        // Тип предмета приоритетнее сырого эмодзи (например, ⚡ у оружия не должно давать energy.png).
        if ($type === 'weapon') {
            return '/img/misc/fight.png';
        }
        if ($icon === '') {
            return '';
        }

        $map = game_icon_map();
        if (isset($map[$icon])) {
            return $map[$icon];
        }

        // Один и тот же символ приходит из БД с селектором вариации U+FE0F и без него.
        $plain = str_replace("\u{FE0F}", '', $icon);
        if (isset($map[$plain])) {
            return $map[$plain];
        }

        $withVs = $plain . "\u{FE0F}";
        if (isset($map[$withVs])) {
            return $map[$withVs];
        }

        // Слаг без папки и расширения ('demon', 'slime', 'demon.') → файл в /img/monsters/ или /img/misc/.
        $slug = rtrim(strtolower($plain), '.');
        if ($slug !== '' && preg_match('/^[a-z0-9_-]{1,64}$/', $slug)) {
            foreach (['/img/monsters/', '/img/weapons/', '/img/shields/', '/img/misc/', '/img/ores/', '/img/tools/'] as $dir) {
                $resolved = game_icon_normalize_path($dir . $slug . '.png');
                if ($resolved !== '') {
                    return $resolved;
                }
            }
        }

        return '';
    }
}

if (! function_exists('game_icon')) {
    /** Рендер иконки: картинка если есть путь/маппинг, иначе текст-эмодзи (никогда в src). */
    function game_icon(string $icon, string $classes = 'h-6 w-6', string $type = ''): string
    {
        $icon = trim($icon);
        $src  = game_icon_src($icon, $type);

        if ($src !== '') {
            return '<img src="' . esc($src) . '" class="shrink-0 object-contain ' . esc($classes) . '" alt="">';
        }

        return '<span class="shrink-0">' . esc($icon) . '</span>';
    }
}