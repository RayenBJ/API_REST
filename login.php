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

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit();
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"] ?? "";
    $password = $_POST["password"] ?? "";

    if (!empty($email) && !empty($password)) {
        if (isset($_POST["register"])) {
            $username = explode('@', $email)[0];
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $token = bin2hex(random_bytes(32));

            try {
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password, token, role) VALUES (?, ?, ?, ?, 'user')");
                $stmt->execute([$username, $email, $hashedPassword, $token]);
                $_SESSION["email"] = $email;
                $_SESSION["token"] = $token;
                $_SESSION["role"] = 'user';
                header("Location: orders.php");
                exit();
            } catch (PDOException $e) {
                $message = "Erreur lors de l'inscription : " . $e->getMessage();
            }
        } elseif (isset($_POST["login"])) {
            $stmt = $pdo->prepare("SELECT id, username, password, token, role FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user["password"])) {
                $_SESSION["email"] = $email;
                $_SESSION["token"] = $user["token"];
                $_SESSION["role"] = $user["role"];
                if ($user["role"] === "admin") {
                    header("Location: admin.php");
                } else {
                    header("Location: orders.php");
                }
                exit();
            } else {
                $message = "Identifiants incorrects !";
            }
        }
    } else {
        $message = "Veuillez remplir tous les champs.";
    }
}

$isLoggedIn = isset($_SESSION["token"]);
$isAdmin = $isLoggedIn && $_SESSION["role"] === "admin";
?>
