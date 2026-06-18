<?php
require_once __DIR__ . '/../../config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$pdo    = db();
$action = $_POST['action'] ?? '';
$id     = (int)($_POST['id'] ?? 0);

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'Invalid application ID']);
    exit();
}

try {
    switch ($action) {

       case 'approve':
    $stmt = $pdo->prepare("
        UPDATE applications
        SET status = 'approved', reviewed_at = NOW()
        WHERE id = ?
    ");
    $stmt->execute([$id]);
    echo json_encode(['success' => true, 'message' => 'Application approved successfully.']);
    break;

    case 'reject':
        $stmt = $pdo->prepare("
            UPDATE applications
            SET status = 'rejected', reviewed_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$id]);
        echo json_encode(['success' => true, 'message' => 'Application rejected.']);
        break;

        case 'delete':
            $stmt = $pdo->prepare("DELETE FROM applications WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['success' => true, 'message' => 'Application deleted.']);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Unknown action.']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}