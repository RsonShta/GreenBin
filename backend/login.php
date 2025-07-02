<?php
require __DIR__ . '/includes/db.php';

session_start();

function sanitize($input): string
{
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        http_response_code(400);
        echo json_encode(['message' => 'Missing email or password.']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT id, first_name, password_hash, role FROM users WHERE email_id = :email");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['first_name'];
            $_SESSION['user_role'] = $user['role'];  // Save role in session

            http_response_code(200);
            echo json_encode([
                'message' => 'Login successful.',
                'user_id' => $user['id'],
                'user_name' => $user['first_name'],
                'role' => $user['role']  // dynamic role from DB
            ]);
        } else {
            http_response_code(401);
            echo json_encode(['message' => 'Invalid email or password.']);
        }
    } catch (PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['message' => 'Internal Server Error.']);
    }
} else {
    http_response_code(405);
    echo json_encode(['message' => 'Method Not Allowed.']);
}
