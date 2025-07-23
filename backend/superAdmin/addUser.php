<?php
session_start();
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json');

// Ensure only superAdmin can perform this action
requireRole(['superAdmin']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['message' => 'Method Not Allowed']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

// CSRF token validation
if (!isset($data['_csrf_token']) || !hash_equals($_SESSION['csrf_token'], $data['_csrf_token'])) {
    http_response_code(403);
    echo json_encode(['message' => 'CSRF token validation failed']);
    exit();
}

$firstName = trim($data['first_name'] ?? '');
$email = filter_var(trim($data['email'] ?? ''), FILTER_VALIDATE_EMAIL);
$phoneNumber = trim($data['phone_number'] ?? '');
$password = $data['password'] ?? '';
$ward = trim($data['ward'] ?? '');
$nagarpalika = trim($data['nagarpalika'] ?? '');
$address = trim($data['address'] ?? '');

if (empty($firstName) || !$email || empty($phoneNumber) || empty($password) || empty($ward) || empty($nagarpalika) || empty($address)) {
    http_response_code(400);
    echo json_encode(['message' => 'All fields are required']);
    exit();
}

if (strlen($password) < 8) {
    http_response_code(400);
    echo json_encode(['message' => 'Password must be at least 8 characters long']);
    exit();
}

try {
    // Check if email already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email_id = :email");
    $stmt->execute([':email' => $email]);
    if ($stmt->fetch()) {
        http_response_code(409);
        echo json_encode(['message' => 'Email already in use']);
        exit();
    }

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    $pdo->beginTransaction();

    $stmt = $pdo->prepare("INSERT INTO users (first_name, email_id, phone_number, password_hash, role) VALUES (:first_name, :email, :phone_number, :password_hash, 'admin')");
    $stmt->execute([
        ':first_name' => $firstName,
        ':email' => $email,
        ':phone_number' => $phoneNumber,
        ':password_hash' => $passwordHash
    ]);

    $userId = $pdo->lastInsertId();

    $adminStmt = $pdo->prepare("INSERT INTO admin_details (user_id, ward, nagarpalika, address) VALUES (:user_id, :ward, :nagarpalika, :address)");
    $adminStmt->execute([
        ':user_id' => $userId,
        ':ward' => $ward,
        ':nagarpalika' => $nagarpalika,
        ':address' => $address
    ]);

    $pdo->commit();

    echo json_encode(['message' => 'Admin added successfully']);

} catch (PDOException $e) {
    $pdo->rollBack();
    error_log("Add user error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['message' => 'Server error']);
}
