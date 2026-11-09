<?php
// Simple PDO check for test user and character
$host = 'MariaDB-11.4';
$db   = 'rpg';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    $stmt = $pdo->prepare('SELECT id, email FROM users WHERE email = ?');
    $stmt->execute(['test@example.com']);
    $u = $stmt->fetch();
    if ($u) {
        echo "USER: ".json_encode($u, JSON_UNESCAPED_UNICODE).PHP_EOL;
        $stmt2 = $pdo->prepare('SELECT * FROM characters WHERE user_id = ?');
        $stmt2->execute([$u->id]);
        $c = $stmt2->fetch();
        echo "CHARACTER: ".($c ? json_encode($c, JSON_UNESCAPED_UNICODE) : 'NONE').PHP_EOL;
    } else {
        echo "USER NOT FOUND".PHP_EOL;
    }
} catch (Throwable $e) {
    echo 'ERROR: '. $e->getMessage() . PHP_EOL;
}
?>