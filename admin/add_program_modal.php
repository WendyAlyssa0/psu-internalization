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
    $partner_id = (int)($_POST['partner_id'] ?? 0);   
    $country_id = (int)($_POST['country_id'] ?? 0);   
    $start_date          = trim($_POST['start_date'] ?? '');
    $end_date   = trim($_POST['end_date'] ?? '');    
    $status              = trim($_POST['status'] ?? '');

        if (
            empty($program_name) ||
            empty($program_type) ||
            empty($description) ||
            $partner_id <= 0 ||
            $country_id <= 0 ||
            empty($start_date) ||
            empty($end_date) ||
            empty($status)
        )
        {
            echo json_encode([
                'success' => false,
                'message' => 'All fields are required.'
            ]);
            exit;
        }

    if ($end_date < $start_date) {
        echo json_encode(['success' => false, 'message' => 'Invalid date range']);
        exit;
    }

try {

    $stmt = $pdo->prepare("
        INSERT INTO programs
        (
            program_name,
            program_type,
            country_id,
            partner_id,
            status,
            start_date,
            _end_date,
            description,
            created_at
        )
        VALUES
        (?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");

    $stmt->execute([
        $program_name,
        $program_type,
        $country_id,
        $partner_id,
        $status,
        $start_date,
        $end_date,
        $description
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Program added'
    ]);

} catch (PDOException $e) {

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);

}
exit;

}
?>