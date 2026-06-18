<?php
require_once __DIR__ . '/../config/db.php';
session_start();

header('Content-Type: application/json');

$pdo = db();
$id = $_POST['id'] ?? null;

if (!$id) {
    echo json_encode(['success' => false]);
    exit;
}

$stmt = $pdo->prepare("UPDATE documents SET status = 'rejected' WHERE id = ?");
$success = $stmt->execute([$id]);

echo json_encode([
    'success' => $success,
    'message' => $success ? 'Document rejected' : 'Failed'
]);