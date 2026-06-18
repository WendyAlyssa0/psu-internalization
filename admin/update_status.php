<?php
require_once __DIR__ . '/../config/db.php';

$pdo = db();

$data = json_decode(file_get_contents("php://input"), true);

$id = $data['id'] ?? null;
$status = $data['status'] ?? null;

if (!$id || !$status) {
    echo json_encode(['success' => false]);
    exit;
}

$stmt = $pdo->prepare("
    UPDATE students
    SET status = ?
    WHERE id = ?
");

$stmt->execute([$status, $id]);

echo json_encode(['success' => true]);