<?php
require __DIR__ . '/includes/db.php';

session_start();

function sanitize($input): string
{
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        http_response_code(400);
        exit("Missing email or password.");
    }

    try {
        $stmt = $pdo->prepare("SELECT id, first_name, password_hash FROM users WHERE email_id = :email");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['first_name'];

            http_response_code(200);
            echo "Login successful.";
        } else {
            http_response_code(401);
            echo "Invalid email or password.";
        }
    } catch (PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        http_response_code(500);
        echo "Internal Server Error.";
    }
} else {
    http_response_code(405);
    echo "Method Not Allowed.";
}
