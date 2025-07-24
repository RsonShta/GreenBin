<?php

require_once __DIR__ . '/Database.php';

class User
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    private function sanitize(string $input): string
    {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    public function register(array $data): array
    {
        $firstName = $this->sanitize($data['first_name'] ?? '');
        $lastName = $this->sanitize($data['last_name'] ?? '');
        $email = strtolower(trim($data['email'] ?? ''));
        $password = $data['password'] ?? '';
        $confirmPassword = $data['confirm_password'] ?? '';
        $phone = $this->sanitize($data['phone_number'] ?? '');

        if (!$firstName || !$lastName || !$email || !$password || !$confirmPassword || !$phone) {
            return ['success' => false, 'message' => 'Missing required fields.', 'code' => 400];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Invalid email address.', 'code' => 400];
        }

        if (strlen($password) < 8) {
            return ['success' => false, 'message' => 'Password must be at least 8 characters.', 'code' => 400];
        }

        if ($password !== $confirmPassword) {
            return ['success' => false, 'message' => 'Passwords do not match.', 'code' => 400];
        }

        if (!preg_match('/^(98|97)\d{8}$/', $phone)) {
            return ['success' => false, 'message' => 'Phone number must start with 98 or 97 and be exactly 10 digits.', 'code' => 400];
        }

        try {
            $checkStmt = $this->pdo->prepare("SELECT email_id, phone_number FROM users WHERE email_id = :email OR phone_number = :phone");
            $checkStmt->execute([':email' => $email, ':phone' => $phone]);
            $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if ($existing) {
                if ($existing['email_id'] === $email) {
                    return ['success' => false, 'message' => 'Email already registered.', 'code' => 409];
                }
                if ($existing['phone_number'] === $phone) {
                    return ['success' => false, 'message' => 'Phone number already registered.', 'code' => 409];
                }
            }

            $passwordHash = password_hash($password, PASSWORD_BCRYPT);

            $stmt = $this->pdo->prepare("
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
                ':role' => 'user',
                ':profile_photo' => 'default.jpg'
            ]);

            $userId = $this->pdo->lastInsertId();

            return ['success' => true, 'message' => 'Registration successful.', 'user_id' => $userId, 'role' => 'user', 'code' => 201];
        } catch (PDOException $e) {
            error_log("Registration Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Internal Server Error.', 'code' => 500];
        }
    }

    public function login(array $data): array
    {
        $email = filter_var($data['email'] ?? '', FILTER_VALIDATE_EMAIL);
        $password = $data['password'] ?? '';

        if (!$email || !$password) {
            return ['success' => false, 'message' => 'Missing email or password.', 'code' => 400];
        }

        try {
            $stmt = $this->pdo->prepare("SELECT id, first_name, password_hash, role FROM users WHERE email_id = :email");
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                if ($user['role'] === 'user') {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['first_name'];
                    $_SESSION['user_role'] = $user['role'];

                    return [
                        'success' => true,
                        'message' => 'Login successful.',
                        'user_id' => $user['id'],
                        'user_name' => $user['first_name'],
                        'role' => $user['role'],
                        'code' => 200
                    ];
                } else {
                    return ['success' => false, 'message' => 'Unauthorized role. Please use the correct login portal.', 'code' => 403];
                }
            } else {
                return ['success' => false, 'message' => 'Invalid email or password.', 'code' => 401];
            }
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Internal Server Error.', 'code' => 500];
        }
    }

    public function requestPasswordReset(string $email): array
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Invalid email address.', 'code' => 400];
        }

        try {
            $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email_id = :email");
            $stmt->execute([':email' => $email]);
            if (!$stmt->fetch()) {
                return ['success' => false, 'message' => 'No user found with that email address.', 'code' => 404];
            }

            $token = bin2hex(random_bytes(32));
            $tokenHash = hash('sha256', $token);
            $expires = time() + 3600;

            $stmt = $this->pdo->prepare("INSERT INTO password_resets (email, token, expires) VALUES (:email, :token, :expires)");
            $stmt->execute([':email' => $email, ':token' => $tokenHash, ':expires' => $expires]);

            $resetLink = "http://" . $_SERVER['HTTP_HOST'] . "/GreenBin/pages/reset-password.php?token=" . $token;

            return [
                'success' => true,
                'message' => 'Password reset link has been generated.',
                'reset_link' => $resetLink,
                'code' => 200
            ];
        } catch (PDOException $e) {
            error_log("Password reset request error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Server error.', 'code' => 500];
        }
    }

    public function resetPassword(string $email, string $password): array
    {
        if (empty($email) || empty($password)) {
            return ['success' => false, 'message' => 'Email and password are required.', 'code' => 400];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Invalid email address.', 'code' => 400];
        }

        if (strlen($password) < 8) {
            return ['success' => false, 'message' => 'Password must be at least 8 characters long.', 'code' => 400];
        }

        try {
            $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email_id = :email");
            $stmt->execute([':email' => $email]);
            if (!$stmt->fetch()) {
                return ['success' => false, 'message' => 'No user found with that email address.', 'code' => 404];
            }

            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            $updateStmt = $this->pdo->prepare("UPDATE users SET password_hash = :password_hash WHERE email_id = :email");
            $updateStmt->execute([':password_hash' => $passwordHash, ':email' => $email]);

            return ['success' => true, 'message' => 'Password has been reset successfully.', 'code' => 200];
        } catch (PDOException $e) {
            error_log("Password update error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Server error.', 'code' => 500];
        }
    }
}
