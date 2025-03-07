<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$dsn = "mysql:host=localhost;dbname=api_rest_db;charset=utf8mb4";
$usernameDB = "jj";
$passwordDB = "Azerty";

try {
    $pdo = new PDO($dsn, $usernameDB, $passwordDB);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

if (!isset($_SESSION["token"])) {
    header("Location: login.php");
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE token = ?");
$stmt->execute([$_SESSION["token"]]);
$adminUser = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$adminUser || $adminUser["role"] !== "admin") {
    die("Accès refusé. Seuls les administrateurs peuvent accéder à cette page.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["delete"])) {
    $userId = $_POST["user_id"];
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    header("Location: admin.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["update_role"])) {
    $userId = $_POST["user_id"];
    $newRole = $_POST["new_role"];
    $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
    $stmt->execute([$newRole, $userId]);
    header("Location: admin.php");
    exit();
}

$stmt = $pdo->query("SELECT id, username, email, role FROM users");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
