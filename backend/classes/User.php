<?php

require_once __DIR__ . '/Database.php';

class User
{
    private PDO $pdo;

    // Constructor initializes the PDO database connection instance
    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    // Sanitize input by trimming and escaping HTML special characters to prevent XSS
    private function sanitize(string $input): string
    {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Register a new user.
     * @param array $data Associative array containing user info from form input
     * @return array Result with success flag, message, HTTP-like status code, and optional verify link
     */
    public function register(array $data): array
    {
        // Sanitize and assign input variables with fallback empty strings
        $firstName = $this->sanitize($data['first_name'] ?? '');
        $lastName = $this->sanitize($data['last_name'] ?? '');
        $email = strtolower(trim($data['email'] ?? ''));
        $password = $data['password'] ?? '';
        $confirmPassword = $data['confirm_password'] ?? '';
        $phone = $this->sanitize($data['phone_number'] ?? '');

        // Check required fields are present
        if (!$firstName || !$lastName || !$email || !$password || !$confirmPassword || !$phone) {
            return ['success' => false, 'message' => 'Missing required fields.', 'code' => 400];
        }

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Invalid email address.', 'code' => 400];
        }

        // Enforce minimum password length
        if (strlen($password) < 8) {
            return ['success' => false, 'message' => 'Password must be at least 8 characters.', 'code' => 400];
        }

        // Confirm password matches
        if ($password !== $confirmPassword) {
            return ['success' => false, 'message' => 'Passwords do not match.', 'code' => 400];
        }

        // Validate phone number format: must start with 98 or 97 and be exactly 10 digits
        if (!preg_match('/^(98|97)\d{8}$/', $phone)) {
            return ['success' => false, 'message' => 'Phone number must start with 98 or 97 and be exactly 10 digits.', 'code' => 400];
        }

        try {
            // Check if email or phone already exists in database
            $checkStmt = $this->pdo->prepare("SELECT email_id, phone_number FROM users WHERE email_id = :email OR phone_number = :phone");
            $checkStmt->execute([':email' => $email, ':phone' => $phone]);
            $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);

            // Return error if email or phone already registered
            if ($existing) {
                if ($existing['email_id'] === $email) {
                    return ['success' => false, 'message' => 'Email already registered.', 'code' => 409];
                }
                if ($existing['phone_number'] === $phone) {
                    return ['success' => false, 'message' => 'Phone number already registered.', 'code' => 409];
                }
            }

            // Hash password securely using bcrypt
            $passwordHash = password_hash($password, PASSWORD_BCRYPT);

            // Generate a random verification token for email confirmation
            $verificationToken = bin2hex(random_bytes(32));

            // Prepare SQL insert statement to add new user to users table
            $stmt = $this->pdo->prepare("
                INSERT INTO users (first_name, last_name, email_id, password_hash, phone_number, country, role, profile_photo, verification_token, is_verified)
                VALUES (:first_name, :last_name, :email, :password_hash, :phone, :country, :role, :profile_photo, :token, 0)
            ");

            // Execute the insert with sanitized and formatted values
            $stmt->execute([
                ':first_name' => ucfirst(strtolower($firstName)), // Capitalize first letter of first name
                ':last_name' => ucfirst(strtolower($lastName)),   // Capitalize first letter of last name
                ':email' => $email,
                ':password_hash' => $passwordHash,
                ':phone' => $phone,
                ':country' => 'NP',           // Hardcoded country code (Nepal)
                ':role' => 'user',            // Default user role
                ':profile_photo' => 'default.jpg', // Default profile photo filename
                ':token' => $verificationToken
            ]);

            // Build verification link to be sent to the user
            $verifyLink = "http://" . $_SERVER['HTTP_HOST'] . "/GreenBin/verify?token=" . $verificationToken;

            // Return success response with verification link (for dev/testing)
            return ['success' => true, 'message' => 'Registration successful. Please verify your email.', 'verify_link' => $verifyLink, 'code' => 201];
        } catch (PDOException $e) {
            // Log DB errors and return generic server error response
            error_log("Registration Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Internal Server Error.', 'code' => 500];
        }
    }

    /**
     * User login function.
     * @param array $data Contains 'email' and 'password' keys
     * @return array Result with success flag, message, HTTP-like status code, and user info on success
     */
    public function login(array $data): array
    {
        // Validate email format and get password from input
        $email = filter_var($data['email'] ?? '', FILTER_VALIDATE_EMAIL);
        $password = $data['password'] ?? '';

        // Check required fields
        if (!$email || !$password) {
            return ['success' => false, 'message' => 'Missing email or password.', 'code' => 400];
        }

        try {
            // Fetch user record by email
            $stmt = $this->pdo->prepare("SELECT id, first_name, password_hash, role, is_verified FROM users WHERE email_id = :email");
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Verify password and check if user exists
            if ($user && password_verify($password, $user['password_hash'])) {
                // Check if email is verified
                if ($user['is_verified'] == 0) {
                    return ['success' => false, 'message' => 'Please verify your email before logging in.', 'code' => 403];
                }

                // Allow only 'user' role to login here
                if ($user['role'] === 'user') {
                    // Store user info in session
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
                    // Reject login if role is unauthorized here
                    return ['success' => false, 'message' => 'Unauthorized role. Please use the correct login portal.', 'code' => 403];
                }
            } else {
                // Incorrect email or password
                return ['success' => false, 'message' => 'Invalid email or password.', 'code' => 401];
            }
        } catch (PDOException $e) {
            // Log DB errors and return server error response
            error_log("Login error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Internal Server Error.', 'code' => 500];
        }
    }

    /**
     * Request password reset by email.
     * @param string $email User's email address
     * @return array Result with success flag, message, HTTP-like status code, and reset link on success
     */
    public function requestPasswordReset(string $email): array
    {
        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Invalid email address.', 'code' => 400];
        }

        try {
            // Check if user exists with provided email
            $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email_id = :email");
            $stmt->execute([':email' => $email]);
            if (!$stmt->fetch()) {
                return ['success' => false, 'message' => 'No user found with that email address.', 'code' => 404];
            }

            // Generate a random reset token and hash it for storage
            $token = bin2hex(random_bytes(32));
            $tokenHash = hash('sha256', $token);
            $expires = time() + 3600; // Token valid for 1 hour

            // Insert token and expiry into password_resets table
            $stmt = $this->pdo->prepare("INSERT INTO password_resets (email, token, expires) VALUES (:email, :token, :expires)");
            $stmt->execute([':email' => $email, ':token' => $tokenHash, ':expires' => $expires]);

            // Create reset password URL with token included
            $resetLink = "http://" . $_SERVER['HTTP_HOST'] . "/GreenBin/reset-password?token=" . $token;

            // Return success and reset link (for dev/testing)
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

    /**
     * Reset the password using the provided token.
     * @param string $token Password reset token from URL
     * @param string $password New password
     * @return array Result with success flag, message, HTTP-like status code
     */
    public function resetPassword(string $token, string $password): array
    {
        // Validate inputs
        if (empty($token) || empty($password)) {
            return ['success' => false, 'message' => 'Token and password are required.', 'code' => 400];
        }

        if (strlen($password) < 8) {
            return ['success' => false, 'message' => 'Password must be at least 8 characters long.', 'code' => 400];
        }

        try {
            // Hash token to compare with DB
            $tokenHash = hash('sha256', $token);

            // Retrieve password reset request if token is valid and not expired
            $stmt = $this->pdo->prepare("SELECT * FROM password_resets WHERE token = :token AND expires >= :now");
            $stmt->execute([':token' => $tokenHash, ':now' => time()]);
            $resetRequest = $stmt->fetch();

            // Return error if token invalid or expired
            if (!$resetRequest) {
                return ['success' => false, 'message' => 'Invalid or expired token.', 'code' => 400];
            }

            $email = $resetRequest['email'];
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            // Begin transaction to update password and delete reset token atomically
            $this->pdo->beginTransaction();

            // Update user's password hash in users table
            $updateStmt = $this->pdo->prepare("UPDATE users SET password_hash = :password_hash WHERE email_id = :email");
            $updateStmt->execute([':password_hash' => $passwordHash, ':email' => $email]);

            // Delete the password reset record to invalidate the token
            $deleteStmt = $this->pdo->prepare("DELETE FROM password_resets WHERE email = :email");
            $deleteStmt->execute([':email' => $email]);

            $this->pdo->commit();

            // Return success
            return ['success' => true, 'message' => 'Password has been reset successfully.', 'code' => 200];
        } catch (PDOException $e) {
            // Rollback transaction if error occurs
            $this->pdo->rollBack();
            error_log("Password update error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Server error.', 'code' => 500];
        }
    }

    /**
     * Verify email using the verification token.
     * @param string $token Verification token from email link
     * @return array Result with success flag, message, HTTP-like status code
     */
    public function verifyEmail(string $token): array
    {
        // Validate token presence
        if (empty($token)) {
            return ['success' => false, 'message' => 'Verification token is required.', 'code' => 400];
        }

        try {
            // Select unverified user by verification token
            $stmt = $this->pdo->prepare("SELECT id FROM users WHERE verification_token = :token AND is_verified = 0");
            $stmt->execute([':token' => $token]);
            $user = $stmt->fetch();

            // Return error if token invalid or user already verified
            if (!$user) {
                return ['success' => false, 'message' => 'Invalid or expired verification token.', 'code' => 400];
            }

            // Update user record to set verified and clear the token
            $updateStmt = $this->pdo->prepare("UPDATE users SET is_verified = 1, verification_token = NULL WHERE id = :id");
            $updateStmt->execute([':id' => $user['id']]);

            // Return success
            return ['success' => true, 'message' => 'Email verified successfully. You can now log in.', 'code' => 200];
        } catch (PDOException $e) {
            error_log("Email verification error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Server error.', 'code' => 500];
        }
    }
}
