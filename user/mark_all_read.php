<?php
require_once __DIR__ . '/../config/db.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

try {
    $pdo  = db();
    $stmt = $pdo->prepare("
        SELECT id FROM applications
        WHERE applicant_id = ?
          AND status IN ('approved', 'rejected')
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (!isset($_SESSION['read_notifications'])) {
        $_SESSION['read_notifications'] = [];
    }

    $before  = count(array_diff($ids, $_SESSION['read_notifications']));
    $_SESSION['read_notifications'] = array_values(array_unique(
        array_merge($_SESSION['read_notifications'], $ids)
    ));

    echo json_encode(['success' => true, 'updated' => $before]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}