<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

include 'connexion.php';

$data = json_decode(file_get_contents('php://input'), true);

$username = $data['username'];
$password = $data['password'];

$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    http_response_code(401);
    echo json_encode(["message" => "Utilisateur non trouvé"]);
    exit;
}

if (password_verify($password, $user['password'])) {
    echo json_encode(["message" => "Connexion réussie"]);
} else {
    http_response_code(401);
    echo json_encode(["message" => "Mot de passe incorrect"]);
}
