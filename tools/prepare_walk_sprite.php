<?php
/**
 * prepare_walk_sprite.php
 *
 * Одноразовая утилита для подготовки 8‑кадрового спрайта «Прогулка».
 * 1. Убирает чёрный фон (RGB < 25) – делает такие пиксели полностью прозрачными.
 * 2. Находит bounding box каждой из 8 фигурок.
 * 3. Вырезает каждую фигурку, масштабирует до 48×48 и центрирует в своём слоте.
 * 4. Сохраняет итоговый спрайт шириной 384px (8×48) и высотой 48px.
 *
 * Запуск из корня проекта:
 *   E:\OSPanel\modules\PHP-8.4\php.exe tools/prepare_walk_sprite.php
 */

$srcPath   = __DIR__ . '/../public/img/misc/character.png'; // исходник с 8 кадрами
$dstPath   = __DIR__ . '/../public/img/misc/walk_sprite.png'; // выход
$frames    = 8;
$slotW     = 48;       // ширина слота кадра в выходном спрайте
$slotH     = 48;       // высота слота
$threshold = 25;       // RGB < threshold → прозрачный

if (!function_exists('imagecreatetruecolor')) {
    fwrite(STDERR, "GD extension is not available.\n");
    exit(1);
}

$src = @imagecreatefrompng($srcPath);
if (!$src) {
    fwrite(STDERR, "Cannot open source image: {$srcPath}\n");
    exit(1);
}

$srcW = imagesx($src);
$srcH = imagesy($src);
$frameW = intdiv($srcW, $frames);

// Создаём выходное изображение: 8 слотов × 48px = 384px шириной, 48px высотой
$dst = imagecreatetruecolor($frames * $slotW, $slotH);
imagealphablending($dst, false);
imagesavealpha($dst, true);
$transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
imagefilledrectangle($dst, 0, 0, $frames * $slotW - 1, $slotH - 1, $transparent);

// Обработка каждого кадра
for ($f = 0; $f < $frames; $f++) {
    $slotX = $f * $frameW;
    
    // 1. Находим bounding box фигурки в этом слоте (пропускаем фон)
    $minX = null; $maxX = null; $minY = null; $maxY = null;
    for ($x = $slotX; $x < $slotX + $frameW; $x++) {
        for ($y = 0; $y < $srcH; $y++) {
            $rgba = imagecolorat($src, $x, $y);
            $r = ($rgba >> 16) & 0xFF;
            $g = ($rgba >> 8) & 0xFF;
            $b = $rgba & 0xFF;
            $a = ($rgba & 0x7F000000) >> 24;
            // Считаем пиксель видимым, если он не чёрный фон и не полностью прозрачный
            if ($a < 127 && !($r < $threshold && $g < $threshold && $b < $threshold)) {
                if ($minX === null || $x < $minX) $minX = $x;
                if ($maxX === null || $x > $maxX) $maxX = $x;
                if ($minY === null || $y < $minY) $minY = $y;
                if ($maxY === null || $y > $maxY) $maxY = $y;
            }
        }
    }
    
    if ($minX === null) {
        fwrite(STDERR, "Frame {$f}: no visible pixels found\n");
        continue;
    }
    
    // 2. Вырезаем фигурку
    $cropW = $maxX - $minX + 1;
    $cropH = $maxY - $minY + 1;
    $cropped = imagecreatetruecolor($cropW, $cropH);
    imagealphablending($cropped, false);
    imagesavealpha($cropped, true);
    $transp = imagecolorallocatealpha($cropped, 0, 0, 0, 127);
    imagefilledrectangle($cropped, 0, 0, $cropW - 1, $cropH - 1, $transp);
    imagecopy($cropped, $src, 0, 0, $minX, $minY, $cropW, $cropH);
    
    // 3. Масштабируем с сохранением пропорций и центрируем в слоте 48×48
    $scale = min($slotW / $cropW, $slotH / $cropH);
    $newW = max(1, (int) round($cropW * $scale));
    $newH = max(1, (int) round($cropH * $scale));
    $scaled = imagecreatetruecolor($newW, $newH);
    imagealphablending($scaled, false);
    imagesavealpha($scaled, true);
    $transp2 = imagecolorallocatealpha($scaled, 0, 0, 0, 127);
    imagefilledrectangle($scaled, 0, 0, $newW - 1, $newH - 1, $transp2);
    imagecopyresampled($scaled, $cropped, 0, 0, 0, 0, $newW, $newH, $cropW, $cropH);

    // 4. Копируем в выходной спрайт: по центру слота, ноги у нижнего края
    imagecopy(
        $dst,
        $scaled,
        $f * $slotW + intdiv($slotW - $newW, 2),
        $slotH - $newH,
        0,
        0,
        $newW,
        $newH
    );
    
    imagedestroy($cropped);
    imagedestroy($scaled);
    
    fwrite(STDOUT, "Frame {$f}: cropped [{$minX}..{$maxX}]×[{$minY}..{$maxY}] ({$cropW}×{$cropH}) → scaled to {$slotW}×{$slotH}\n");
}

if (!imagepng($dst, $dstPath)) {
    fwrite(STDERR, "Failed to save processed sprite to {$dstPath}\n");
    exit(1);
}

imagedestroy($src);
imagedestroy($dst);

echo "Sprite processed and saved to {$dstPath}\n";
?>