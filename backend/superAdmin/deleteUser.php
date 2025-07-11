<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole(['superAdmin']);

require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$userId = $data['user_id'] ?? null;

if (!$userId) {
    http_response_code(400);
    echo json_encode(['message' => 'User ID is required.']);
    exit();
}

if ($userId == $_SESSION['user_id']) {
    http_response_code(403);
    echo json_encode(['message' => 'You cannot delete your own account.']);
    exit();
}

try {
    // Verify user exists and is not superAdmin
    $stmt = $pdo->prepare("SELECT role FROM users WHERE id = :id");
    $stmt->execute([':id' => $userId]);
    $user = $stmt->fetch();

    if (!$user) {
        http_response_code(404);
        echo json_encode(['message' => 'User not found.']);
        exit();
    }

    if ($user['role'] === 'superAdmin') {
        http_response_code(403);
        echo json_encode(['message' => 'Cannot delete a superAdmin account.']);
        exit();
    }

    $oldRole = $user['role'];

    // Delete user
    $deleteStmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
    $deleteStmt->execute([':id' => $userId]);

    // Log deletion with old role as old_value, new_value null
    $logStmt = $pdo->prepare("
        INSERT INTO admin_logs (admin_id, action, target_user_id, old_value, new_value, created_at)
        VALUES (:admin_id, 'user_deleted', :target_user_id, :old_val, NULL, NOW())
    ");
    $logStmt->execute([
        ':admin_id' => $_SESSION['user_id'],
        ':target_user_id' => $userId,
        ':old_val' => $oldRole
    ]);

    echo json_encode(['message' => 'User deleted successfully.']);
} catch (PDOException $e) {
    error_log("deleteUser error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['message' => 'Internal server error.']);
}
