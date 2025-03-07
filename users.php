<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(200);
    exit();
}

header("Content-Type: application/json");
include 'connexion.php';

if (!isset($_GET['token'])) {
    http_response_code(401);
    echo json_encode(["message" => "Token manquant"]);
    exit;
}

$token = $_GET['token'];

$stmt = $pdo->prepare("SELECT * FROM users WHERE token = ?");
$stmt->execute([$token]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    http_response_code(403);
    echo json_encode(["message" => "Token invalide"]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            if ($user['role'] !== 'admin' && $user['id'] != $_GET['id']) {
                http_response_code(403);
                echo json_encode(["message" => "Accès refusé"]);
                exit;
            }
            $stmt = $pdo->prepare("SELECT id, username, email, role FROM users WHERE id = ?");
            $stmt->execute([$_GET['id']]);
            $userData = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode($userData ?: ["message" => "Utilisateur non trouvé"]);
        } else {
            if ($user['role'] !== 'admin') {
                http_response_code(403);
                echo json_encode(["message" => "Accès refusé"]);
                exit;
            }
            $stmt = $pdo->query("SELECT id, username, email, role FROM users");
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        if (!isset($data['username'], $data['email'], $data['password'])) {
            http_response_code(400);
            echo json_encode(["message" => "Champs manquants"]);
            exit;
        }
        $token = bin2hex(random_bytes(32));
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, token, role) VALUES (?, ?, ?, ?, 'user')");
        $stmt->execute([
            $data['username'],
            $data['email'],
            password_hash($data['password'], PASSWORD_DEFAULT),
            $token
        ]);
        echo json_encode(["message" => "Utilisateur ajouté", "token" => $token]);
        break;
}
