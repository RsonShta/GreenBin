<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/GreenBin/backend/includes/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/GreenBin/backend/includes/auth.php';

// Require admin role
requireRole(['admin'], '/GreenBin/login');

// Language Switch
if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'np'])) {
    $_SESSION['lang'] = $_GET['lang'];
}
$lang = $_SESSION['lang'] ?? 'en';

// Fetch current user details for the form
$userId = $_SESSION['user_id'];
$stmt = $pdo->prepare("
    SELECT u.first_name, u.last_name, u.email_id, u.phone_number, u.profile_photo, u.role,
           ad.ward, ad.nagarpalika, ad.address, ad.office_phone, ad.department, ad.employee_id
    FROM users u
    LEFT JOIN admin_details ad ON u.id = ad.user_id
    WHERE u.id = ?
");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("User not found.");
}

// Initialize variables for messages
$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = ucfirst(strtolower(trim($_POST['first_name'])));
    $last_name = ucfirst(strtolower(trim($_POST['last_name'])));
    $phone_number = trim($_POST['phone_number']);
    $profile_photo = $user['profile_photo']; // keep old if not changed

    // Validate phone number
    if (!preg_match('/^(98|97)\d{8}$/', $phone_number)) {
        $error = $lang === 'np' ? "फोन नम्बर सही छैन।" : "Invalid phone number.";
    } else {
        // Handle photo upload if new one provided
        if (!empty($_FILES['profile_photo']['name'])) {
            $targetDir = $_SERVER['DOCUMENT_ROOT'] . "/GreenBin/uploads/profile_photo/";
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0777, true);
            }
            $fileName = time() . "_" . basename($_FILES["profile_photo"]["name"]);
            $targetFile = $targetDir . $fileName;

            if (move_uploaded_file($_FILES["profile_photo"]["tmp_name"], $targetFile)) {
                $profile_photo = $fileName;
            } else {
                $error = $lang === 'np' ? "फोटो अपलोड असफल भयो।" : "Failed to upload photo.";
            }
        }

        if (!$error) {
            $pdo->beginTransaction();
            try {
                $update = $pdo->prepare("UPDATE users SET first_name=?, last_name=?, phone_number=?, profile_photo=? WHERE id=?");
                $update->execute([$first_name, $last_name, $phone_number, $profile_photo, $_SESSION['user_id']]);

                $ward = trim($_POST['ward']);
                $nagarpalika = trim($_POST['nagarpalika']);
                $address = trim($_POST['address']);
                $office_phone = trim($_POST['office_phone']);
                $department = trim($_POST['department']);
                $employee_id = trim($_POST['employee_id']);

                // Check if admin_details record exists
                $checkAdminDetails = $pdo->prepare("SELECT COUNT(*) FROM admin_details WHERE user_id = ?");
                $checkAdminDetails->execute([$userId]);
                $adminDetailsExists = $checkAdminDetails->fetchColumn();

                if ($adminDetailsExists) {
                    // Update existing admin_details
                    $adminUpdate = $pdo->prepare("
                        UPDATE admin_details 
                        SET ward = ?, nagarpalika = ?, address = ?, office_phone = ?, department = ?, employee_id = ?
                        WHERE user_id = ?
                    ");
                    $adminUpdate->execute([$ward, $nagarpalika, $address, $office_phone, $department, $employee_id, $userId]);
                } else {
                    // Insert new admin_details
                    $adminInsert = $pdo->prepare("
                        INSERT INTO admin_details (user_id, ward, nagarpalika, address, office_phone, department, employee_id)
                        VALUES (?, ?, ?, ?, ?, ?, ?)
                    ");
                    $adminInsert->execute([$userId, $ward, $nagarpalika, $address, $office_phone, $department, $employee_id]);
                }
                
                $pdo->commit();

                $_SESSION['user_name'] = $first_name . " " . $last_name; // update session name
                $success = $lang === 'np' ? "प्रोफाइल सफलतापूर्वक अपडेट भयो!" : "Profile updated successfully!";
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = "An error occurred: " . $e->getMessage();
            }

            // Refresh user data
            $user['first_name'] = $first_name;
            $user['last_name'] = $last_name;
            $user['phone_number'] = $phone_number;
            $user['profile_photo'] = $profile_photo;
            $user['ward'] = $ward;
            $user['nagarpalika'] = $nagarpalika;
            $user['address'] = $address;
            $user['office_phone'] = $office_phone;
            $user['department'] = $department;
            $user['employee_id'] = $employee_id;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $lang === 'np' ? 'एडमिन प्रोफाइल सम्पादन गर्नुहोस्' : 'Edit Admin Profile' ?> - GreenBin Nepal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet" />
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { inter: ['Inter', 'sans-serif'] },
                    colors: {
                        primary: '#2e7d32',
                        dark: '#1b5e20',
                        light: '#f0fdf4'
                    }
                }
            }
        }
    </script>
    <style>
        html,
        body {
            scrollbar-width: none;
            /* Firefox */
        }

        html::-webkit-scrollbar,
        body::-webkit-scrollbar {
            width: 0;
            height: 0;
        }
    </style>
</head>

<body class="bg-gray-100 min-h-screen font-inter">

    <header class="bg-white shadow-sm border-b border-gray-200 py-4 px-6 flex justify-between items-center">
        <div class="flex items-center gap-3">
            <img src="/GreenBin/frontend/img/mountain.png" class="w-8 h-8" alt="Logo" />
            <div>
                <h1 class="text-lg font-bold text-green-700 leading-tight">हरित नेपाल</h1>
                <span class="text-xs text-gray-500">GreenBin Nepal</span>
            </div>
        </div>
        <div class="flex items-center gap-4 text-sm">
            <a href="?lang=<?= $lang === 'en' ? 'np' : 'en' ?>"
                class="text-xs px-2 py-1 border rounded hover:bg-gray-100 transition">
                <?= $lang === 'en' ? 'नेपाली' : 'English' ?>
            </a>
            <a href="/GreenBin/adminProfile" class="text-gray-700 hover:text-green-700 transition">
                <?= $lang === 'np' ? 'प्रोफाइल' : 'Profile' ?>
            </a>
            <a href="/GreenBin/backend/logout.php"
                class="border px-3 py-1 rounded-md text-gray-800 hover:bg-gray-100 flex items-center gap-1 text-sm transition">
                <?= $lang === 'np' ? 'लग आउट' : 'Logout' ?>
            </a>
        </div>
    </header>

    <main class="max-w-4xl mx-auto bg-white shadow-md rounded-lg p-6 mt-2 mb-10">
        <a href="/GreenBin/adminProfile"
            class="inline-block text-green-700 hover:text-white hover:bg-green-700 transition text-xs border border-green-700 rounded px-1.5 py-0.5 mb-4">
            <?= $lang === 'np' ? 'प्रोफाइलमा फर्कनुहोस्' : '<-- Back to Profile' ?>
        </a>
        <h2 class="text-xl font-bold mt-2 mb-6">
            <?= $lang === 'np' ? 'एडमिन प्रोफाइल सम्पादन गर्नुहोस्' : 'Edit Admin Profile' ?>
        </h2>

        <?php if ($error): ?>
            <p class="text-red-600 text-sm mb-4"><?= htmlspecialchars($error) ?></p>
        <?php elseif ($success): ?>
            <p class="text-green-600 text-sm mb-4"><?= htmlspecialchars($success) ?></p>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- First Name -->
                <div>
                    <label for="first_name" class="block text-sm font-medium mb-1">
                        <?= $lang === 'np' ? 'नाम' : 'First Name' ?>
                    </label>
                    <input type="text" id="first_name" name="first_name" required
                        value="<?= htmlspecialchars($user['first_name']) ?>"
                        class="w-full border border-gray-300 p-2 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500" />
                </div>

                <!-- Last Name -->
                <div>
                    <label for="last_name" class="block text-sm font-medium mb-1">
                        <?= $lang === 'np' ? 'थर' : 'Last Name' ?>
                    </label>
                    <input type="text" id="last_name" name="last_name" required
                        value="<?= htmlspecialchars($user['last_name']) ?>"
                        class="w-full border border-gray-300 p-2 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500" />
                </div>

                <!-- Phone -->
                <div>
                    <label for="phone_number" class="block text-sm font-medium mb-1">
                        <?= $lang === 'np' ? 'फोन नम्बर' : 'Phone Number' ?>
                    </label>
                    <input type="text" id="phone_number" name="phone_number" required
                        value="<?= htmlspecialchars($user['phone_number']) ?>"
                        class="w-full border border-gray-300 p-2 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500" />
                </div>

                <!-- Email (disabled) -->
                <div>
                    <label for="email" class="block text-sm font-medium mb-1">
                        Email (<?= $lang === 'np' ? 'परिवर्तन गर्न मिल्दैन' : 'Cannot be changed' ?>)
                    </label>
                    <input type="email" id="email" disabled value="<?= htmlspecialchars($user['email_id']) ?>"
                        class="w-full border border-gray-200 bg-gray-100 p-2 rounded-md cursor-not-allowed" />
                </div>
            </div>

            <h3 class="text-lg font-semibold mt-6 mb-4">
                <?= $lang === 'np' ? 'प्रशासन विवरण' : 'Admin Details' ?>
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Ward -->
                <div>
                    <label for="ward" class="block text-sm font-medium mb-1">Ward</label>
                    <input type="text" id="ward" name="ward" value="<?= htmlspecialchars($user['ward'] ?? '') ?>" class="w-full border border-gray-300 p-2 rounded-md" required>
                </div>
                <!-- Nagarpalika -->
                <div>
                    <label for="nagarpalika" class="block text-sm font-medium mb-1">Nagarpalika</label>
                    <input type="text" id="nagarpalika" name="nagarpalika" value="<?= htmlspecialchars($user['nagarpalika'] ?? '') ?>" class="w-full border border-gray-300 p-2 rounded-md" required>
                </div>
                <!-- Address -->
                <div>
                    <label for="address" class="block text-sm font-medium mb-1">Address</label>
                    <input type="text" id="address" name="address" value="<?= htmlspecialchars($user['address'] ?? '') ?>" class="w-full border border-gray-300 p-2 rounded-md" required>
                </div>
                <!-- Office Phone -->
                <div>
                    <label for="office_phone" class="block text-sm font-medium mb-1">Office Phone</label>
                    <input type="text" id="office_phone" name="office_phone" value="<?= htmlspecialchars($user['office_phone'] ?? '') ?>" class="w-full border border-gray-300 p-2 rounded-md" required>
                </div>
                <!-- Department -->
                <div>
                    <label for="department" class="block text-sm font-medium mb-1">Department</label>
                    <input type="text" id="department" name="department" value="<?= htmlspecialchars($user['department'] ?? '') ?>" class="w-full border border-gray-300 p-2 rounded-md" required>
                </div>
                <!-- Employee ID -->
                <div>
                    <label for="employee_id" class="block text-sm font-medium mb-1">Employee ID</label>
                    <input type="text" id="employee_id" name="employee_id" value="<?= htmlspecialchars($user['employee_id'] ?? '') ?>" class="w-full border border-gray-300 p-2 rounded-md" required>
                </div>
            </div>

            <!-- Profile Photo -->
            <div class="mt-6">
                <label class="block text-sm font-medium mb-1">
                    <?= $lang === 'np' ? 'प्रोफाइल फोटो' : 'Profile Photo' ?>
                </label>
                <div class="flex items-center gap-4">
                    <img id="preview"
                        src="/GreenBin/uploads/profile_photo/<?= htmlspecialchars($user['profile_photo'] ?: 'default.png') ?>"
                        alt="Profile" class="w-16 h-16 rounded-full border object-cover" />
                    <input type="file" name="profile_photo" accept="image/*" id="profile_photo"
                        class="w-full border border-gray-300 p-1 rounded-md cursor-pointer" />
                </div>
            </div>

            <button type="submit"
                class="bg-green-700 hover:bg-green-800 text-white w-full py-2 rounded-md font-semibold transition">
                <?= $lang === 'np' ? 'परिवर्तनहरू सुरक्षित गर्नुहोस्' : 'Save Changes' ?>
            </button>
        </form>
    </main>

</body>
</html>
