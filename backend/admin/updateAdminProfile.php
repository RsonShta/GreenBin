<?php

require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../classes/Database.php'; // Include Database for admin_details update

session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
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

$userId = $_SESSION['user_id'];
$user = new User();
$pdo = Database::getInstance(); // Get PDO instance for admin_details

// First, update the main user profile
$result = $user->updateProfile($userId, $_POST, $_FILES);

if ($result['success']) {
    // If user profile update was successful, proceed with admin details
    $ward = htmlspecialchars(trim($_POST['ward'] ?? ''));
    $nagarpalika = htmlspecialchars(trim($_POST['nagarpalika'] ?? ''));
    $address = htmlspecialchars(trim($_POST['address'] ?? ''));

    try {
        $stmt = $pdo->prepare("
            INSERT INTO admin_details (user_id, ward, nagarpalika, address)
            VALUES (:user_id, :ward, :nagarpalika, :address)
            ON DUPLICATE KEY UPDATE ward = VALUES(ward), nagarpalika = VALUES(nagarpalika), address = VALUES(address)
        ");
        $stmt->execute([
            ':user_id' => $userId,
            ':ward' => $ward,
            ':nagarpalika' => $nagarpalika,
            ':address' => $address
        ]);
        // Admin details updated successfully
        http_response_code($result['code']); // Use the code from user update
        echo json_encode($result); // Use the message from user update
    } catch (PDOException $e) {
        error_log("DB Error updating admin details: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error occurred while updating admin details.']);
    }
} else {
    // If user profile update failed, return its result
    http_response_code($result['code']);
    echo json_encode($result);
}
