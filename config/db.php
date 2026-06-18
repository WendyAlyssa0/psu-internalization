<?php

if (!function_exists('db')) {

    function db(): PDO {

        static $pdo = null;

        if ($pdo instanceof PDO) {
            return $pdo;
        }

        $host = '127.0.0.1';
        $port = 3306;
        $db   = 'internalization_management';
        $user = 'root';
        $pass = '';

        try {
            $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";

            $pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);

            return $pdo;

        } catch (PDOException $e) {
            die("Database Connection Failed: " . $e->getMessage());
        }
    }
}