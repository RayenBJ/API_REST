<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

header("Content-Type: application/json; charset=UTF-8");
try {
    $pdo = new PDO('mysql:host=localhost;dbname=api_rest_db;charset=utf8mb4', 'api_user', 'Azerty');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
    exit;
}
