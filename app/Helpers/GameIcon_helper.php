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

if (! function_exists('game_icon_src')) {
    /** Путь к картинке для иконки: сам путь, затем иконка по типу предмета, затем маппинг эмодзи → PNG. Иначе ''. */
    function game_icon_src(string $icon, string $type = ''): string
    {
        $icon = trim($icon);
        if ($icon !== '' && game_icon_is_path($icon)) {
            return $icon;
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