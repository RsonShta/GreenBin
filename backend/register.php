<?php
require_once 'db.php';

// Utility function to sanitize input
function sanitize($input): string
{
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Input validation
    $firstName = sanitize($_POST['first_name'] ?? '');
    $lastName = sanitize($_POST['last_name'] ?? '');
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';
    $phone = sanitize($_POST['phone_number'] ?? '');

    if (!$firstName || !$lastName || !$email || !$password) {
        http_response_code(400);
        exit("Missing required fields.");
    }

    if (strlen($password) < 8) {
        http_response_code(400);
        exit("Password must be at least 8 characters.");
    }

    // Hash the password securely
    $passwordHash = password_hash($password, PASSWORD_BCRYPT);

    // Insert user into database
    try {
        $stmt = $pdo->prepare("
            INSERT INTO users (first_name, last_name, email_id, password_hash, phone_number, country)
            VALUES (:first_name, :last_name, :email, :password_hash, :phone, :country)
        ");

        $stmt->execute([
            ':first_name' => $firstName,
            ':last_name' => $lastName,
            ':email' => $email,
            ':password_hash' => $passwordHash,
            ':phone' => $phone,
            ':country' => $country
        ]);

        http_response_code(201);
        echo "✅ User registered successfully.";
    } catch (PDOException $e) {
        if ($e->errorInfo[1] === 1062) {
            // Duplicate entry
            http_response_code(409);
            echo "❌ Email already registered.";
        } else {
            error_log("Registration error: " . $e->getMessage());
            http_response_code(500);
            echo "❌ Internal Server Error.";
        }
    }
} else {
    http_response_code(405);
    echo "❌ Method not allowed.";
}
?>