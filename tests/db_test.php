<?php
require_once __DIR__ . '/db.php';

header('Content-Type: text/plain; charset=utf-8');

// expose DSN/vars if available
$vars = ['DB_HOST', 'DB_USER', 'DB_PASS', 'DB_NAME'];
foreach ($vars as $v) {
    if (isset($GLOBALS[$v])) {
        echo $v . '=' . (string)$GLOBALS[$v] . "\n";
    }
}

try {
    $pdo = db();
    echo "DB_OK\n";

    $stmt = $pdo->query("SELECT DATABASE() as db");
    $row = $stmt->fetch();
    echo "Connected DB: " . ($row['db'] ?? 'unknown') . "\n";
} catch (Throwable $e) {
    echo "DB_FAIL\n";
    echo 'Message: ' . $e->getMessage() . "\n";
    echo 'Class: ' . get_class($e) . "\n";

    if ($e instanceof PDOException) {
        echo 'SQLSTATE: ' . ($e->getCode() ?? '') . "\n";
        // PDO driver message might include host/socket/auth issues
        echo 'PDOException: ' . $e->getMessage() . "\n";
    }

    echo "Trace:\n";
    echo $e->getTraceAsString() . "\n";
}

