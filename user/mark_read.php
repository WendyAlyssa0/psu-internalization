<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$id   = (int)($data['id'] ?? 0);

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid ID']);
    exit();
}

if (!isset($_SESSION['read_notifications'])) {
    $_SESSION['read_notifications'] = [];
}

if (!in_array($id, $_SESSION['read_notifications'])) {
    $_SESSION['read_notifications'][] = $id;
}

echo json_encode(['success' => true]);