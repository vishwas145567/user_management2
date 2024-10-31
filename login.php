<?php
require 'vendor/autoload.php'; 
use \Firebase\JWT\JWT;

$host = 'localhost';
$db = 'user_management2';
$user = 'root'; 
$pass = ''; 

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $email = $input['email'];
    $password = $input['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $key = "your_secret_key"; 
        $payload = [
            'id' => $user['id'],
            'email' => $user['email'],
            'iat' => time(),
            'exp' => time() + (60 * 60) 
        ];

        $jwt = JWT::encode($payload, $key);
        echo json_encode(['token' => $jwt]);
    } else {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid email or password.']);
    }
}
