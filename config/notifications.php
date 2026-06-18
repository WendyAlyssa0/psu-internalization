<?php

function createNotification(
    PDO $pdo,
    int $userId,
    string $type,
    string $title,
    string $message
) {
    $stmt = $pdo->prepare("
        INSERT INTO notifications
        (
            user_id,
            type,
            title,
            message,
            is_read,
            created_at
        )
        VALUES
        (
            ?,
            ?,
            ?,
            ?,
            0,
            NOW()
        )
    ");

    return $stmt->execute([
        $userId,
        $type,
        $title,
        $message
    ]);
}