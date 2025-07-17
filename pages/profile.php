<?php
require $_SERVER['DOCUMENT_ROOT'] . '/GreenBin/backend/includes/auth.php';
requireRole(['superAdmin', 'admin', 'user']);

require $_SERVER['DOCUMENT_ROOT'] . '/GreenBin/backend/includes/db.php';

// Language switch
if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'np'])) {
    $_SESSION['lang'] = $_GET['lang'];
}
$lang = $_SESSION['lang'] ?? 'en';

// Fetch logged-in user details
$userId = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT first_name, last_name, email_id, phone_number, created_at, role, profile_photo
                       FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("User not found.");
}

// Fallback profile photo
$profilePhoto = !empty($user['GreenBin/uploads/profile_photo'])
    ? "/GreenBin/uploads/profile_photo" . htmlspecialchars($user['profile_photo'])
    : "/GreenBin/frontend/img/default_profile.png";
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?= $lang === 'np' ? 'प्रोफाइल - हरित नेपाल' : 'Profile - GreenBin Nepal' ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet" />
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="bg-gray-100 min-h-screen">
    <!-- Header -->

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
                class="text-xs px-2 py-1 border rounded hover:bg-gray-100">
                🌐 <?= $lang === 'en' ? 'नेपाली' : 'English' ?>
            </a>
            <a href="/GreenBin/backend/logout.php"
                class="border px-3 py-1 rounded-md text-gray-800 hover:bg-gray-100 flex items-center gap-1 text-sm">
                <i class="fas fa-sign-out-alt"></i> <?= $lang === 'np' ? 'लग आउट' : 'Logout' ?>
            </a>
        </div>
    </header>

    <!-- Profile Card -->
    <main class="max-w-xl mx-auto bg-white mt-10 p-6 rounded-lg shadow">
        <div class="flex flex-col items-center">
            <img src="<?= $profilePhoto ?>" alt="Profile Photo"
                class="w-24 h-24 rounded-full border-2 border-green-600 mb-4 object-cover" />

            <h2 class="text-xl font-bold text-green-700">
                <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?>
            </h2>
            <p class="text-sm text-gray-600"><?= ucfirst($user['role']) ?></p>
        </div>
        <div class="mt-6 space-y-1 text-sm text-gray-700">
            <p>
                <span class="font-medium"><?= $lang === 'np' ? 'इमेल: ' : 'Email: ' ?></span>
                <?= htmlspecialchars($user['email_id']) ?>
            </p>
            <p>
                <span class="font-medium"><?= $lang === 'np' ? 'फोन: ' : 'Phone: ' ?></span>
                <?= htmlspecialchars($user['phone_number']) ?>
            </p>
            <p>
                <span class="font-medium"><?= $lang === 'np' ? 'खाता बनाएको मिति: ' : 'Joined: ' ?></span>
                <?= date('F j, Y', strtotime($user['created_at'])) ?>
            </p>
        </div>


        <div class="mt-6 flex justify-between">
            <a href="/GreenBin/dashboard" class="text-sm bg-gray-200 px-4 py-2 rounded hover:bg-gray-300">
                &#8592; <?= $lang === 'np' ? 'ड्यासबोर्डमा फर्कनुहोस्' : 'Back to Dashboard' ?>
            </a>
            <a href="/GreenBin/editProfile"
                class="text-sm bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                <?= $lang === 'np' ? 'प्रोफाइल सम्पादन गर्नुहोस्' : 'Edit Profile' ?>
            </a>
        </div>
    </main>

    <script src="https://kit.fontawesome.com/3f471bb5a5.js" crossorigin="anonymous"></script>
</body>

</html>