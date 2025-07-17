<?php
require $_SERVER['DOCUMENT_ROOT'] . '/GreenBin/backend/includes/auth.php';
requireRole(['superAdmin', 'admin', 'user']);
require $_SERVER['DOCUMENT_ROOT'] . '/GreenBin/backend/includes/db.php';

// Language handling
if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'np'])) {
    $_SESSION['lang'] = $_GET['lang'];
}
$lang = $_SESSION['lang'] ?? 'en';

$userId = $_SESSION['user_id'];

// Fetch current user details
$stmt = $pdo->prepare("SELECT first_name, last_name, email_id, phone_number, profile_photo FROM users WHERE id = ?");
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

            // Optional: Validate file type and size here if you want

            if (move_uploaded_file($_FILES["profile_photo"]["tmp_name"], $targetFile)) {
                $profile_photo = $fileName;
            } else {
                $error = $lang === 'np' ? "फोटो अपलोड असफल भयो।" : "Failed to upload photo.";
            }
        }

        if (!$error) {
            $update = $pdo->prepare("UPDATE users SET first_name=?, last_name=?, phone_number=?, profile_photo=? WHERE id=?");
            $update->execute([$first_name, $last_name, $phone_number, $profile_photo, $userId]);

            $_SESSION['user_name'] = $first_name . " " . $last_name; // update session name

            $success = $lang === 'np' ? "प्रोफाइल सफलतापूर्वक अपडेट भयो!" : "Profile updated successfully!";

            // Refresh user data
            $user['first_name'] = $first_name;
            $user['last_name'] = $last_name;
            $user['phone_number'] = $phone_number;
            $user['profile_photo'] = $profile_photo;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="<?= $lang ?>">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?= $lang === 'np' ? 'प्रोफाइल सम्पादन' : 'Edit Profile' ?> - हरित नेपाल</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet" />
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="bg-gradient-to-br from-blue-50 via-white to-green-50 min-h-screen">

    <header class="bg-white shadow-sm border-b border-gray-200 py-4 px-6 flex justify-between items-center">
        <div class="flex items-center gap-3">
            <img src="/GreenBin/frontend/img/mountain.png" alt="Logo" class="w-8 h-8" />
            <div>
                <h1 class="text-lg font-bold text-green-700">हरित नेपाल</h1>
                <span class="text-xs text-gray-500">GreenBin Nepal</span>
            </div>
        </div>

        <div class="flex items-center gap-4 text-sm">
            <a href="?lang=<?= $lang === 'en' ? 'np' : 'en' ?>"
                class="border px-3 py-1 rounded-md text-gray-700 hover:bg-gray-100 transition">
                🌐 <?= $lang === 'en' ? 'नेपाली' : 'English' ?>
            </a>

        </div>
    </header>

    <main class="max-w-lg mx-auto bg-white shadow-md rounded-lg p-6 mt-2 mb-10">
        <a href="/GreenBin/dashboard"
            class="inline-block text-green-700 hover:text-white hover:bg-green-700 transition text-xs border border-green-700 rounded px-1.5 py-0.5 mb-4">
            <?= $lang === 'np' ? 'ड्यासबोर्डमा फर्कनुहोस्' : '<-- Back to Dashboard' ?>
        </a>
        <h2 class="text-xl font-bold mt-2 mb-6">
            <?= $lang === 'np' ? 'प्रोफाइल सम्पादन गर्नुहोस्' : 'Edit Your Profile' ?>
        </h2>

        <?php if ($error): ?>
            <p class="text-red-600 text-sm mb-4"><?= htmlspecialchars($error) ?></p>
        <?php elseif ($success): ?>
            <p class="text-green-600 text-sm mb-4"><?= htmlspecialchars($success) ?></p>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="space-y-5">
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

            <!-- Email (disabled) -->
            <div>
                <label for="email" class="block text-sm font-medium mb-1">
                    Email (<?= $lang === 'np' ? 'परिवर्तन गर्न मिल्दैन' : 'Cannot be changed' ?>)
                </label>
                <input type="email" id="email" disabled value="<?= htmlspecialchars($user['email_id']) ?>"
                    class="w-full border border-gray-200 bg-gray-100 p-2 rounded-md cursor-not-allowed" />
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

            <!-- Profile Photo -->
            <div>
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

    <script>
        // Live preview of profile photo before upload
        document.getElementById('profile_photo').addEventListener('change', function (event) {
            const file = event.target.files[0];
            if (!file) return;

            if (!file.type.startsWith('image/')) {
                alert('Please select a valid image file.');
                event.target.value = '';
                return;
            }

            const reader = new FileReader();
            reader.onload = function (e) {
                document.getElementById('preview').src = e.target.result;
            };
            reader.readAsDataURL(file);
        });
    </script>

</body>

</html>