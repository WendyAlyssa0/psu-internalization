<?php
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

$pdo = db();

$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("
    SELECT *
    FROM programs
    WHERE id = ?
");

$stmt->execute([$id]);

$program = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$program) {
    echo json_encode([
        'error' => 'Program not found'
    ]);
    exit;
}

echo json_encode($program);