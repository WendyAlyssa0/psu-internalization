<?php
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

try {
    $pdo = db();

    $id = $_POST['edit_id'] ?? 0;

    $stmt = $pdo->prepare("
        UPDATE partners SET
        institution_name=?,
        country=?,
        contact_person=?,
        contact_email=?,
        agreement_type=?,
        expiry_date=?,
        status=?,
        notes=?
        WHERE id=?
    ");

    $stmt->execute([
        $_POST['institution_name'],
        $_POST['country'],
        $_POST['contact_person'],
        $_POST['contact_email'],
        $_POST['agreement_type'],
        $_POST['expiry_date'],
        $_POST['status'],
        $_POST['notes'],
        $id
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Partner updated successfully'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}