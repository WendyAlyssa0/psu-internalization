<?php
require_once __DIR__ . '/../config/db.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$id = $data['id'] ?? null;

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'Invalid ID']);
    exit;
}

$pdo = db();

$stmt = $pdo->prepare("
    UPDATE notifications
    SET is_read = 1
    WHERE id = ? AND user_id = ?
");

$success = $stmt->execute([$id, $_SESSION['user_id']]);

echo json_encode(['success' => $success]);