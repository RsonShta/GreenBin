<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole(['superAdmin']);

require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

// CSRF Protection
if (!isset($_SESSION['csrf_token']) || !isset($data['_csrf_token']) || !hash_equals($_SESSION['csrf_token'], $data['_csrf_token'])) {
    http_response_code(403);
    echo json_encode(['message' => 'Invalid CSRF token.']);
    exit();
}
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

    // Start a transaction to ensure atomicity
    $pdo->beginTransaction();

    try {
        // First, delete associated reports to satisfy foreign key constraints.
        $deleteReportsStmt = $pdo->prepare("DELETE FROM reports WHERE user_id = :user_id");
        $deleteReportsStmt->execute([':user_id' => $userId]);

        // Then, delete the user
        $deleteUserStmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
        $deleteUserStmt->execute([':id' => $userId]);
        
        // Finally, log the deletion now that the admin_logs table exists.
        $logStmt = $pdo->prepare("
            INSERT INTO admin_logs (admin_id, action, target_user_id, old_value, new_value, created_at)
            VALUES (:admin_id, 'user_deleted', :target_user_id, :old_val, NULL, NOW())
        ");
        $logStmt->execute([
            ':admin_id' => $_SESSION['user_id'],
            ':target_user_id' => $userId,
            ':old_val' => $oldRole
        ]);

        // If all good, commit the transaction
        $pdo->commit();

        echo json_encode(['message' => 'User deleted successfully.']);

    } catch (PDOException $e) {
        // If something goes wrong, roll back the transaction
        $pdo->rollBack();
        throw $e; // Re-throw the exception to be caught by the outer catch block
    }

} catch (PDOException $e) {
    error_log("deleteUser error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['message' => 'Internal server error.']);
}
