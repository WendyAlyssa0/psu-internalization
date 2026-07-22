<?php
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

try {

    $pdo = db();

    $country     = trim($_POST['country'] ?? '');
    $institution = trim($_POST['institution_name'] ?? '');
    $agreement   = trim($_POST['agreement_type'] ?? '');
    $status      = trim($_POST['status'] ?? 'active');
    $contact     = trim($_POST['contact_person'] ?? '');
    $email       = trim($_POST['contact_email'] ?? '');
    $expiry      = trim($_POST['expiry_date'] ?? '');
    $notes       = trim($_POST['notes'] ?? '');

    if (empty($country) || empty($institution)) {
        echo json_encode([
            'success' => false,
            'message' => 'Country and Institution are required.'
        ]);
        exit;
    }

    $stmt = $pdo->prepare("
        INSERT INTO partners
        (
            institution_name,
            country,
            contact_person,
            contact_email,
            agreement_type,
            expiry_date,
            status,
            notes
        )
        VALUES
        (?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $institution,
        $country,
        $contact ?: null,
        $email ?: null,
        $agreement ?: null,
        $expiry ?: null,
        $status,
        $notes ?: null
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Partner added successfully.'
    ]);

} catch (PDOException $e) {

    echo json_encode([
        'success' => false,
        'message' => 'Database Error: ' . $e->getMessage()
    ]);

} catch (Exception $e) {

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}