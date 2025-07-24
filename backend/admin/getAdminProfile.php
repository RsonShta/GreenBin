<?php

require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../includes/auth.php';

session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['message' => 'Method Not Allowed']);
    exit;
}

// Ensure only authenticated admins can access
requireRole('admin', '/GreenBin/adminLogin');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['message' => 'Admin not authenticated.']);
    exit;
}

$user = new User();
$result = $user->getUserById($_SESSION['user_id']);

// If user is found, fetch admin_details
if ($result['success'] && $result['user']['role'] === 'admin') {
    try {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("SELECT ward, nagarpalika, address FROM admin_details WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $_SESSION['user_id']]);
        $adminDetails = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($adminDetails) {
            $result['user'] = array_merge($result['user'], $adminDetails);
        }
    } catch (PDOException $e) {
        error_log("DB Error fetching admin details: " . $e->getMessage());
        // Continue without admin details if there's a DB error
    }
}

http_response_code($result['code']);
echo json_encode($result);
