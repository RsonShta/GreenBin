<?php
require_once 'db.php';
session_start();

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

// Utility function to sanitize input
function sanitize($input): string {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Input validation
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        http_response_code(400);
        exit("❌ Missing required fields.");
    }

    try {
        // Check if user exists
        $stmt = $pdo->prepare("SELECT id, first_name, last_name, password_hash FROM users WHERE email_id = :email");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            http_response_code(401);
            exit("❌ Invalid email or password.");
        }

        // Verify password
        if (password_verify($password, $user['password_hash'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
            
            // Check if student profile exists
            $stmt = $pdo->prepare("SELECT id FROM students WHERE user_id = :user_id");
            $stmt->execute([':user_id' => $user['id']]);
            
            if ($stmt->rowCount() === 0) {
                // Create default student profile
                $stmt = $pdo->prepare("
                    INSERT INTO students (user_id, full_name, email, phone, course) 
                    VALUES (:user_id, :full_name, :email, :phone, :course)
                ");
                $stmt->execute([
                    ':user_id' => $user['id'],
                    ':full_name' => $_SESSION['user_name'],
                    ':email' => $email,
                    ':phone' => "0000000000",
                    ':course' => "Not Selected"
                ]);
            }
            
            http_response_code(200);
            echo "✅ Login successful!";
            // You can redirect here if not using AJAX
            // header("Location: dashboard.php");
            exit();
        } else {
            http_response_code(401);
            exit("❌ Invalid email or password.");
        }
    } catch (PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        http_response_code(500);
        exit("❌ Internal Server Error.");
    }
} else {
    http_response_code(405);
    exit("❌ Method not allowed.");
}
?>