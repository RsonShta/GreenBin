<?php
require __DIR__ . '/../includes/db.php'; // Adjust path to db.php
session_start();
header('Content-Type: application/json');

// Sanitize helper
function sanitize($input): string
{
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = sanitize($_POST['first_name'] ?? '');
    $lastName = sanitize($_POST['last_name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $phone = sanitize($_POST['phone_number'] ?? '');
    $ward = sanitize($_POST['ward'] ?? '');
    $nagarpalika = sanitize($_POST['nagarpalika'] ?? '');
    $address = sanitize($_POST['address'] ?? '');

    // Basic validation for admin fields
    if (!$firstName || !$lastName || !$email || !$password || !$confirmPassword || !$phone || !$ward || !$nagarpalika || !$address) {
        http_response_code(400);
        echo json_encode(['message' => 'Missing required fields for admin registration.']);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['message' => 'Invalid email address.']);
        exit;
    }

    if (strlen($password) < 8) {
        http_response_code(400);
        echo json_encode(['message' => 'Password must be at least 8 characters.']);
        exit;
    }

    if ($password !== $confirmPassword) {
        http_response_code(400);
        echo json_encode(['message' => 'Passwords do not match.']);
        exit;
    }

    if (!preg_match('/^(98|97)\d{8}$/', $phone)) {
        http_response_code(400);
        echo json_encode(['message' => 'Phone number must start with 98 or 97 and be exactly 10 digits.']);
        exit;
    }

    try {
        // Check if email or phone already exists
        $checkStmt = $pdo->prepare("SELECT email_id, phone_number FROM users WHERE email_id = :email OR phone_number = :phone");
        $checkStmt->execute([':email' => $email, ':phone' => $phone]);
        $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            if ($existing['email_id'] === $email) {
                http_response_code(409);
                echo json_encode(['message' => 'Email already registered.']);
                exit;
            }
            if ($existing['phone_number'] === $phone) {
                http_response_code(409);
                echo json_encode(['message' => 'Phone number already registered.']);
                exit;
            }
        }

        // Insert new user with 'admin' role
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        $pdo->beginTransaction();

        $stmt = $pdo->prepare("
            INSERT INTO users (first_name, last_name, email_id, password_hash, phone_number, country, role, profile_photo)
            VALUES (:first_name, :last_name, :email, :password_hash, :phone, :country, :role, :profile_photo)
        ");

        $stmt->execute([
            ':first_name' => ucfirst(strtolower($firstName)),
            ':last_name' => ucfirst(strtolower($lastName)),
            ':email' => $email,
            ':password_hash' => $passwordHash,
            ':phone' => $phone,
            ':country' => 'NP',
            ':role' => 'admin', // Set role to admin
            ':profile_photo' => 'default.jpg'
        ]);

        $userId = $pdo->lastInsertId();

        // Insert admin-specific details
        $adminStmt = $pdo->prepare("
            INSERT INTO admin_details (user_id, ward, nagarpalika, address)
            VALUES (:user_id, :ward, :nagarpalika, :address)
        ");
        $adminStmt->execute([
            ':user_id' => $userId,
            ':ward' => $ward,
            ':nagarpalika' => $nagarpalika,
            ':address' => $address
        ]);

        $pdo->commit();

        http_response_code(201);
        echo json_encode([
            'message' => 'Admin registration successful.',
            'user_id' => $userId,
            'role' => 'admin'
        ]);
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Admin Registration Error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['message' => 'Internal Server Error.']);
    }
} else {
    http_response_code(405);
    echo json_encode(['message' => 'Method not allowed.']);
}
