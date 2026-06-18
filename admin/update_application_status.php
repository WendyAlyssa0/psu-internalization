<?php
header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';

$pdo = db();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit();
}

/* INPUT */
$data = json_decode(file_get_contents('php://input'), true);
if (!is_array($data)) {
    $data = $_POST;
}

$id = isset($data['id']) ? (int) $data['id'] : null;
$status = $data['status'] ?? null;
$action = $data['action'] ?? null;

$admin_id = $_SESSION['user_id'] ?? null;

if (!$admin_id) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

/* DELETE */
if ($action === 'delete') {
    $stmt = $pdo->prepare('DELETE FROM applications WHERE id = ?');
    $stmt->execute([$id]);

    echo json_encode(['success' => true, 'message' => 'Deleted']);
    exit();
}

/* UPDATE */
$valid = ['approved', 'rejected', 'submitted', 'under_review'];

if (!$id || !$status || !in_array($status, $valid)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit();
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare(
        'UPDATE applications SET status = ?, updated_at = NOW() WHERE id = ?'
    );
    $stmt->execute([$status, $id]);

    $log = $pdo->prepare(
        'INSERT INTO audit_logs (admin_id, module, record_id, action, created_at) VALUES (?, \'applications\', ?, ?, NOW())'
    );

    $log->execute([$admin_id, $id, 'status_' . $status]);

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Updated successfully'
    ]);
} catch (Exception $e) {
    $pdo->rollBack();

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error',
        'debug' => $e->getMessage()
    ]);
}
