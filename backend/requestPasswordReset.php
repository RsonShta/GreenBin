<?php
require __DIR__ . '/includes/db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['message' => 'Method Not Allowed']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$email = filter_var($data['email'] ?? '', FILTER_VALIDATE_EMAIL);

if (!$email) {
    http_response_code(400);
    echo json_encode(['message' => 'Invalid email address.']);
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email_id = :email");
    $stmt->execute([':email' => $email]);
    if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode(['message' => 'No user found with that email address.']);
        exit();
    }

    // Generate a secure token
    $token = bin2hex(random_bytes(32));
    $tokenHash = hash('sha256', $token);

    // Set expiration time (e.g., 1 hour from now)
    $expires = time() + 3600;

    // Store the token in the database
    $stmt = $pdo->prepare("INSERT INTO password_resets (email, token, expires) VALUES (:email, :token, :expires)");
    $stmt->execute([':email' => $email, ':token' => $tokenHash, ':expires' => $expires]);

    // In a real application, you would email this link.
    // For this development environment, we will return it in the response.
    $resetLink = "http://" . $_SERVER['HTTP_HOST'] . "/GreenBin/reset-password?token=" . $token;

    echo json_encode([
        'message' => 'Password reset link has been generated.',
        'reset_link' => $resetLink
    ]);

} catch (PDOException $e) {
    error_log("Password reset request error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['message' => 'Server error.']);
}
