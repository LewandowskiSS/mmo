<?php

if (! function_exists('game_icon')) {
    /** Render image-path icons as images, otherwise render the supplied emoji. */
    function game_icon(string $icon, string $classes = 'h-6 w-6'): string
    {
        $icon = trim($icon);
        if (str_starts_with($icon, '/')) {
            return '<img src="' . esc($icon) . '" class="shrink-0 object-contain ' . esc($classes) . '" alt="">';
        }

        return '<span class="shrink-0">' . esc($icon) . '</span>';
    }
}