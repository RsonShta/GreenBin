<?php
require __DIR__ . '/includes/db.php';

header('Content-Type: application/json');
session_start();

// Sanitize helper
function sanitize($input): string
{
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = sanitize($_POST['first_name'] ?? '');
    $lastName = sanitize($_POST['last_name'] ?? '');
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $phone = sanitize($_POST['phone_number'] ?? '');

    if (!$firstName || !$lastName || !$email || !$password || !$confirmPassword || !$phone) {
        http_response_code(400);
        echo json_encode(['message' => 'Missing required fields.']);
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


    // Check for existing email or phone number
    try {
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

        // All good, insert user
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        $stmt = $pdo->prepare("
            INSERT INTO users (first_name, last_name, email_id, password_hash, phone_number, country, role)
            VALUES (:first_name, :last_name, :email, :password_hash, :phone, :country, :role)
        ");

        $stmt->execute([
            ':first_name' => $firstName,
            ':last_name' => $lastName,
            ':email' => $email,
            ':password_hash' => $passwordHash,
            ':phone' => $phone,
            ':country' => 'NP',
            ':role' => 'user'
        ]);

        $userId = $pdo->lastInsertId();

        http_response_code(201);
        echo json_encode([
            'message' => 'Registration successful.',
            'user_id' => $userId,
            'role' => 'user'
        ]);
    } catch (PDOException $e) {
        error_log("Registration Error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['message' => 'Internal Server Error.']);
    }
} else {
    http_response_code(405);
    echo json_encode(['message' => 'Method not allowed.']);
}
