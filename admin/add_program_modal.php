<?php
require_once __DIR__ . '/../config/db.php';

$pdo = db();
$error = '';

function e($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $program_name        = trim($_POST['program_name'] ?? '');
    $program_type        = trim($_POST['program_type'] ?? '');
    $description         = trim($_POST['description'] ?? '');
    $partner_institution = trim($_POST['partner_institution'] ?? '');
    $country             = trim($_POST['country'] ?? '');
    $start_date          = trim($_POST['start_date'] ?? '');
    $end_date            = trim($_POST['end_date'] ?? '');
    $status              = trim($_POST['status'] ?? '');

    if (
        empty($program_name) ||
        empty($program_type) ||
        empty($description) ||
        empty($partner_institution) ||
        empty($country) ||
        empty($start_date) ||
        empty($end_date) ||
        empty($status)
    ) {
        echo json_encode(['success' => false, 'message' => 'All fields required']);
        exit;
    }

    if ($end_date < $start_date) {
        echo json_encode(['success' => false, 'message' => 'Invalid date range']);
        exit;
    }

    $stmt = $pdo->prepare("
        INSERT INTO programs
        (program_name, program_type, description,
         partner_institution, country,
         start_date, end_date, status, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");

    $stmt->execute([
        $program_name, $program_type, $description,
        $partner_institution, $country,
        $start_date, $end_date, $status
    ]);

    echo json_encode(['success' => true, 'message' => 'Program added']);
    exit;
}
?>