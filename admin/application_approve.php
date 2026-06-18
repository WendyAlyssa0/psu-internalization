<?php
require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json');

$pdo = db();

$id = $_POST['id'] ?? null;

if (!$id) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing ID'
    ]);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE applications SET status = 'approved' WHERE id = ?");
    $stmt->execute([$id]);

    echo json_encode([
        'success' => true,
        'message' => 'Application approved successfully'
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}