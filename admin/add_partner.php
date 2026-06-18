<?php
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

try {
    $pdo = db();

    $country = $_POST['country'] ?? '';
    $institution = $_POST['institution_name'] ?? '';
    $agreement = $_POST['agreement_type'] ?? null;
    $status = $_POST['status'] ?? 'active';
    $contact = $_POST['contact_person'] ?? null;
    $email = $_POST['contact_email'] ?? null;
    $expiry = $_POST['expiry_date'] ?? null;
    $notes = $_POST['notes'] ?? null;

    if (!$country || !$institution) {
        echo json_encode([
            'success' => false,
            'message' => 'Country and Institution are required.'
        ]);
        exit;
    }
        $stmt = $pdo->prepare("
            INSERT INTO partners
            (institution_name, country, contact_person, agreement_type, expiry_date, status)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $institution,
            $country,
            $contact,
            $agreement,
            $expiry,
            $status,
            $notes
        ]);

    echo json_encode([
        'success' => true,
        'message' => 'Partner added successfully'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}