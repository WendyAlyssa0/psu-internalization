<?php
require_once __DIR__ . '/../config/db.php';

$pdo = db();

$data = json_decode(file_get_contents("php://input"), true);

$id = $data['id'] ?? null;
$direction = $data['direction'] ?? null;
$reason = $data['reason'] ?? null;

if (!$id || !$direction) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

$stages = [
    'submitted',
    'coordinator_review',
    'dean_approval',
    'iao_approval',
    'final_endorsement',
    'completed'
];

$stmt = $pdo->prepare("SELECT approval_stage FROM students WHERE id = ?");
$stmt->execute([$id]);
$current = $stmt->fetchColumn();

if (!$current) {
    echo json_encode(['success' => false, 'message' => 'Student not found']);
    exit;
}

$index = array_search($current, $stages);

if ($direction === 'forward') {
    if ($index < count($stages) - 1) {
        $new = $stages[$index + 1];
    } else {
        $new = $current;
    }
}

elseif ($direction === 'back') {
    if ($index > 0) {
        $new = $stages[$index - 1];
    } else {
        $new = $current;
    }
}

elseif ($direction === 'reject') {
    $new = 'rejected';
} else {
    echo json_encode(['success' => false]);
    exit;
}

$stmt = $pdo->prepare("
    UPDATE students
    SET approval_stage = ?, remarks = ?
    WHERE id = ?
");

$stmt->execute([$new, $reason, $id]);

echo json_encode(['success' => true]);
