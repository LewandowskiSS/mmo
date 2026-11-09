<?php
// Диагностика сида: ищем тестового пользователя и персонажа, пишем в файл отчёта.
$out = 'E:/OSPanel/home/mmo.local/temp/check_user_out.txt';
$lines = [];

try {
    // Bootstrap CodeIgniter (console mode) to use Config::connect()
    require 'vendor/autoload.php';
    require 'app/Config/Paths.php';
    $paths = new \Config\Paths();
    // Load system bootstrap
    require $paths->systemDirectory . '/Boot.php';
    \CodeIgniter\Boot::bootConsole($paths);

    $db = \Config\Database::connect();
    $user = $db->table('users')->where('email', 'test@example.com')->get()->getRow();
    $lines[] = 'user: ' . json_encode($user, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($user) {
        $char = $db->table('characters')->where('user_id', $user->id)->get()->getRow();
        $lines[] = 'character: ' . json_encode($char, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    } else {
        $lines[] = 'character: NO USER FOUND';
    }
} catch (Throwable $e) {
    $lines[] = 'ERROR: ' . get_class($e) . ': ' . $e->getMessage();
}

file_put_contents($out, implode(PHP_EOL, $lines));
echo 'written';
