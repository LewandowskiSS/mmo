<?php
// Проверка спрайта: размер + bbox каждого из 8 кадров (первая/последняя занятая колонка и строка).
$path = $argv[1] ?? __DIR__ . '/../public/img/misc/walk_sprite.png';
$im = imagecreatefrompng($path);
$w = imagesx($im); $h = imagesy($im);
echo basename($path) . " size: {$w}x{$h}\n";
$frameW = intdiv($w, 8);
for ($f = 0; $f < 8; $f++) {
    $start = $f * $frameW;
    $firstC = null; $lastC = null; $firstR = null; $lastR = null;
    for ($x = $start; $x < $start + $frameW; $x++) {
        for ($y = 0; $y < $h; $y++) {
            $c = imagecolorat($im, $x, $y);
            if ((($c & 0x7F000000) >> 24) < 127) {
                if ($firstC === null) $firstC = $x;
                $lastC = $x;
                if ($firstR === null || $y < $firstR) $firstR = $y;
                if ($lastR === null || $y > $lastR) $lastR = $y;
            }
        }
    }
    if ($firstC === null) { echo sprintf("frame %d: EMPTY\n", $f); continue; }
    echo sprintf(
        "frame %d: bbox x[%d..%d] y[%d..%d]  w=%d h=%d  (slot [%d..%d), margin L=%d R=%d B=%d T=%d)\n",
        $f, $firstC, $lastC, $firstR, $lastR,
        $lastC - $firstC + 1, $lastR - $firstR + 1, $start, $start + $frameW,
        $firstC - $start, ($start + $frameW - 1) - $lastC, ($h - 1) - $lastR, $firstR
    );
}
