<?php
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

try {
    $pdo = db();
    $country_id = filter_input(INPUT_GET, 'country_id', FILTER_VALIDATE_INT) ?? 0;

    $stmt = $pdo->prepare(
        "SELECT id, institution_name
         FROM institutions
         WHERE country_id = ?
         ORDER BY institution_name"
    );

    $stmt->execute([$country_id]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([]);
}