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

$token = $_SESSION["token"];
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_name = $_POST["product_name"] ?? "";
    $amount = $_POST["amount"] ?? "";

    if (!empty($product_name) && !empty($amount)) {
        try {
            $stmtUser = $pdo->prepare("SELECT id FROM users WHERE token = :token");
            $stmtUser->bindParam(":token", $token);
            $stmtUser->execute();
            $user = $stmtUser->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                $message = "Erreur : Token invalide.";
            } else {
                $stmt = $pdo->prepare("INSERT INTO orders (user_id, product_name, amount, status) VALUES (:user_id, :product_name, :amount, 'pending')");
                $stmt->bindParam(":user_id", $user["id"]);
                $stmt->bindParam(":product_name", $product_name);
                $stmt->bindParam(":amount", $amount);
                $stmt->execute();
                $message = "Commande passée avec succès !";
            }
        } catch (PDOException $e) {
            $message = "Erreur SQL : " . $e->getMessage();
        }
    } else {
        $message = "Veuillez remplir tous les champs.";
    }
}
