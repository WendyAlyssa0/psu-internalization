<?php
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

$pdo = db();

$id = $_POST['id'] ?? null;
$status = $_POST['status'] ?? null;

if (!$id || !$status) {
    echo json_encode(['success' => false]);
    exit;
}

$stmt = $pdo->prepare("UPDATE travel_info SET travel_status = ? WHERE id = ?");
$ok = $stmt->execute([$status, $id]);

echo json_encode(['success' => $ok]);