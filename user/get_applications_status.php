<?php
session_start();
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode([]);
    exit();
}

try {
    $pdo = db();
    $user_id = $_SESSION['user_id'];

    $stmt = $pdo->prepare("
        SELECT id, purpose, status, documents_status, submitted_at
        FROM applications
        WHERE applicant_id = ?
        ORDER BY id DESC
    ");

    $stmt->execute([$user_id]);

    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([]);
}
