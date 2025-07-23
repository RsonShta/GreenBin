<?php
require __DIR__ . '/includes/db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['message' => 'Method Not Allowed']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$token = $data['token'] ?? '';
$password = $data['password'] ?? '';

if (empty($token) || empty($password)) {
    http_response_code(400);
    echo json_encode(['message' => 'Token and password are required.']);
    exit();
}

if (strlen($password) < 8) {
    http_response_code(400);
    echo json_encode(['message' => 'Password must be at least 8 characters long.']);
    exit();
}

try {
    $tokenHash = hash('sha256', $token);

    $stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token = :token AND expires >= :now");
    $stmt->execute([':token' => $tokenHash, ':now' => time()]);
    $resetRequest = $stmt->fetch();

    if (!$resetRequest) {
        http_response_code(400);
        echo json_encode(['message' => 'Invalid or expired token.']);
        exit();
    }

    $email = $resetRequest['email'];
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    $pdo->beginTransaction();

    // Update user's password
    $updateStmt = $pdo->prepare("UPDATE users SET password_hash = :password_hash WHERE email_id = :email");
    $updateStmt->execute([':password_hash' => $passwordHash, ':email' => $email]);

    // Delete the used token
    $deleteStmt = $pdo->prepare("DELETE FROM password_resets WHERE email = :email");
    $deleteStmt->execute([':email' => $email]);

    $pdo->commit();

    echo json_encode(['message' => 'Password has been reset successfully.']);

} catch (PDOException $e) {
    $pdo->rollBack();
    error_log("Password update error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['message' => 'Server error.']);
}
