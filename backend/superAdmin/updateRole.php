<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole(['superAdmin']);

require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json');

// CSRF Protection
$input_data = json_decode(file_get_contents('php://input'), true);

if (!$input_data) {
    http_response_code(400);
    echo json_encode(['message' => 'Invalid JSON input.']);
    exit();
}

if (!isset($_SESSION['csrf_token']) || !isset($input_data['_csrf_token']) || $_SESSION['csrf_token'] !== $input_data['_csrf_token']) {
    http_response_code(403);
    echo json_encode(['message' => 'Invalid CSRF token.']);
    exit();
}

// Remove CSRF token from data
unset($input_data['_csrf_token']);

// Use $input_data for subsequent operations
$data = $input_data;

$userId = $data['user_id'] ?? null;
$newRole = $data['new_role'] ?? null;

$validRoles = ['user', 'admin'];

if (!$userId || !$newRole || !in_array($newRole, $validRoles)) {
    http_response_code(400);
    echo json_encode(['message' => 'Invalid user ID or role.']);
    exit();
}

try {
    // Check user existence and current role
    $stmt = $pdo->prepare("SELECT role FROM users WHERE id = :id");
    $stmt->execute([':id' => $userId]);
    $oldRole = $stmt->fetchColumn();

    if (!$oldRole) {
        http_response_code(404);
        echo json_encode(['message' => 'User not found.']);
        exit();
    }

    if ($oldRole === 'superAdmin') {
        http_response_code(403);
        echo json_encode(['message' => 'Cannot change role of superAdmin.']);
        exit();
    }

    if ($userId == $_SESSION['user_id']) {
        http_response_code(403);
        echo json_encode(['message' => 'Cannot change your own role.']);
        exit();
    }

    // Update role
    $updateStmt = $pdo->prepare("UPDATE users SET role = :role WHERE id = :id");
    $updateStmt->execute([':role' => $newRole, ':id' => $userId]);

    // Log the role change with old and new values
    $logStmt = $pdo->prepare("
        INSERT INTO admin_logs (admin_id, action, target_user_id, old_value, new_value, created_at)
        VALUES (:admin_id, 'role_change', :target_id, :old_val, :new_val, NOW())
    ");
    $logStmt->execute([
        ':admin_id' => $_SESSION['user_id'],
        ':target_id' => $userId,
        ':old_val' => $oldRole,
        ':new_val' => $newRole
    ]);

    echo json_encode(['message' => 'Role updated successfully.']);
} catch (PDOException $e) {
    error_log("updateRole error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['message' => 'Internal server error.']);
}
