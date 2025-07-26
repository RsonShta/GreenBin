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
            $verificationToken = bin2hex(random_bytes(32));

            $stmt = $this->pdo->prepare("
                INSERT INTO users (first_name, last_name, email_id, password_hash, phone_number, country, role, profile_photo, verification_token, is_verified)
                VALUES (:first_name, :last_name, :email, :password_hash, :phone, :country, :role, :profile_photo, :token, 0)
            ");

            $stmt->execute([
                ':first_name' => ucfirst(strtolower($firstName)),
                ':last_name' => ucfirst(strtolower($lastName)),
                ':email' => $email,
                ':password_hash' => $passwordHash,
                ':phone' => $phone,
                ':country' => 'NP',
                ':role' => 'user',
                ':profile_photo' => 'default.jpg',
                ':token' => $verificationToken
            ]);

            $verifyLink = "http://" . $_SERVER['HTTP_HOST'] . "/GreenBin/verify?token=" . $verificationToken;

            return ['success' => true, 'message' => 'Registration successful. Please verify your email.', 'verify_link' => $verifyLink, 'code' => 201];
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
            $stmt = $this->pdo->prepare("SELECT id, first_name, password_hash, role, is_verified FROM users WHERE email_id = :email");
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password_hash'])) {
                if ($user['is_verified'] == 0) {
                    return ['success' => false, 'message' => 'Please verify your email before logging in.', 'code' => 403];
                }
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

            $resetLink = "http://" . $_SERVER['HTTP_HOST'] . "/GreenBin/reset-password?token=" . $token;

            // For non-production, we can return the link directly.
            return [
                'success' => true,
                'message' => 'Password reset link generated successfully.',
                'reset_link' => $resetLink,
                'code' => 200
            ];
        } catch (PDOException $e) {
            error_log("Password reset request error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Server error.', 'code' => 500];
        }
    }

    public function resetPassword(string $token, string $password): array
    {
        if (empty($token) || empty($password)) {
            return ['success' => false, 'message' => 'Token and password are required.', 'code' => 400];
        }

        if (strlen($password) < 8) {
            return ['success' => false, 'message' => 'Password must be at least 8 characters long.', 'code' => 400];
        }

        try {
            $tokenHash = hash('sha256', $token);

            $stmt = $this->pdo->prepare("SELECT * FROM password_resets WHERE token = :token AND expires >= :now");
            $stmt->execute([':token' => $tokenHash, ':now' => time()]);
            $resetRequest = $stmt->fetch();

            if (!$resetRequest) {
                return ['success' => false, 'message' => 'Invalid or expired token.', 'code' => 400];
            }

            $email = $resetRequest['email'];
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            $this->pdo->beginTransaction();

            $updateStmt = $this->pdo->prepare("UPDATE users SET password_hash = :password_hash WHERE email_id = :email");
            $updateStmt->execute([':password_hash' => $passwordHash, ':email' => $email]);

            $deleteStmt = $this->pdo->prepare("DELETE FROM password_resets WHERE email = :email");
            $deleteStmt->execute([':email' => $email]);

            $this->pdo->commit();

            return ['success' => true, 'message' => 'Password has been reset successfully.', 'code' => 200];
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Password update error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Server error.', 'code' => 500];
        }
    }

    public function verifyEmail(string $token): array
    {
        if (empty($token)) {
            return ['success' => false, 'message' => 'Verification token is required.', 'code' => 400];
        }

        try {
            $stmt = $this->pdo->prepare("SELECT id FROM users WHERE verification_token = :token AND is_verified = 0");
            $stmt->execute([':token' => $token]);
            $user = $stmt->fetch();

            if (!$user) {
                return ['success' => false, 'message' => 'Invalid or expired verification token.', 'code' => 400];
            }

            $updateStmt = $this->pdo->prepare("UPDATE users SET is_verified = 1, verification_token = NULL WHERE id = :id");
            $updateStmt->execute([':id' => $user['id']]);

            return ['success' => true, 'message' => 'Email verified successfully. You can now log in.', 'code' => 200];
        } catch (PDOException $e) {
            error_log("Email verification error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Server error.', 'code' => 500];
        }
    }
}
