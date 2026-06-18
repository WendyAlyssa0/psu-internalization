<?php
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

try {
    $pdo = db();

    if (!isset($_GET['id'])) {
        echo json_encode(["error" => "Missing ID"]);
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM partners WHERE id = ?");
    $stmt->execute([$_GET['id']]);

    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$data) {
        echo json_encode(["error" => "Not found"]);
        exit;
    }

    echo json_encode($data);

} catch (Exception $e) {
    echo json_encode([
        "error" => $e->getMessage()
    ]);
}