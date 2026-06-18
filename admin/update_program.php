<?php
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

try {
    $pdo = db();

    $stmt = $pdo->prepare("
        UPDATE programs SET
        program_name=?,
        program_type=?,
        status=?,
        partner_institution=?,
        country=?,
        start_date=?,
        end_date=?,
        description=?
        WHERE id=?
    ");

    $stmt->execute([
        $_POST['program_name'],
        $_POST['program_type'],
        $_POST['status'],
        $_POST['partner_institution'],
        $_POST['country'],
        $_POST['start_date'],
        $_POST['end_date'],
        $_POST['description'],
        $_POST['id']
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Program updated successfully'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}