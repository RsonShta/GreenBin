<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['message' => 'Method not allowed']);
    exit();
}

$email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
$password = $_POST['password'] ?? '';

if (!$email || !$password) {
    http_response_code(400);
    echo json_encode(['message' => 'Email and password are required']);
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT id, first_name, password_hash, role FROM users WHERE email_id = :email");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();

    if ($user && $user['role'] === 'superAdmin' && password_verify($password, $user['password_hash'])) {
        // Set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['first_name'];
        $_SESSION['user_role'] = $user['role'];

        // Redirect to superAdmin dashboard or return JSON success
        // Here JSON for AJAX login (if you want)
echo json_encode(['message' => 'Login successful', 'redirect' => '/GreenBin/pages/manageUsers.php', 'role' => $user['role']]);
    } else {
        http_response_code(401);
        echo json_encode(['message' => 'Invalid credentials or not authorized']);
    }
} catch (PDOException $e) {
    error_log("Login error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['message' => 'Server error']);
}
