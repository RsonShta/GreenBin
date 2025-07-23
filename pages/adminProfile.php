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

// Fetch logged-in admin details
$userId = $_SESSION['user_id'];

// Fetch user details and admin-specific details
$stmt = $pdo->prepare("
    SELECT 
        u.first_name, u.last_name, u.email_id, u.phone_number, u.created_at, u.role, u.profile_photo,
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
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $lang === 'np' ? 'एडमिन प्रोफाइल' : 'Admin Profile' ?> - GreenBin Nepal</title>
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

<!-- Profile Card -->
<main class="max-w-xl mx-auto bg-white mt-10 p-6 rounded-lg shadow">
    <div class="flex flex-col items-center">
        <?php
        // Determine the profile photo path
        $profilePhoto = !empty($user['profile_photo']) ? '/GreenBin/uploads/profile_photo/' . htmlspecialchars($user['profile_photo']) : '/GreenBin/frontend/img/default_profile.png';
        ?>
        <img src="<?= $profilePhoto ?>" alt="Profile Photo"
            class="w-24 h-24 rounded-full border-2 border-green-600 mb-4 object-cover" />

        <h2 class="text-xl font-bold text-green-700">
            <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?>
        </h2>
        <p class="text-sm text-gray-600"><?= ucfirst($user['role']) ?></p>
    </div>
    <div class="mt-6 space-y-1 text-sm text-gray-700">
        <p>
            <span class="font-medium">Email: </span>
            <?= htmlspecialchars($user['email_id']) ?>
        </p>
        <p>
            <span class="font-medium">Phone: </span>
            <?= htmlspecialchars($user['phone_number']) ?>
        </p>
        <p>
            <span class="font-medium">Joined: </span>
            <?= date('F j, Y', strtotime($user['created_at'])) ?>
        </p>
        <?php if ($user['role'] === 'admin'): ?>
        <p>
            <span class="font-medium">Ward: </span>
            <?= htmlspecialchars($user['ward'] ?? 'N/A') ?>
        </p>
        <p>
            <span class="font-medium">Nagarpalika: </span>
            <?= htmlspecialchars($user['nagarpalika'] ?? 'N/A') ?>
        </p>
        <p>
            <span class="font-medium">Address: </span>
            <?= htmlspecialchars($user['address'] ?? 'N/A') ?>
        </p>
        <p>
            <span class="font-medium">Office Phone: </span>
            <?= htmlspecialchars($user['office_phone'] ?? 'N/A') ?>
        </p>
        <p>
            <span class="font-medium">Department: </span>
            <?= htmlspecialchars($user['department'] ?? 'N/A') ?>
        </p>
        <p>
            <span class="font-medium">Employee ID: </span>
            <?= htmlspecialchars($user['employee_id'] ?? 'N/A') ?>
        </p>
        <?php endif; ?>
    </div>


    <div class="mt-6 flex justify-between">
<a href="/GreenBin/adminDashboard" class="text-sm bg-gray-200 px-4 py-2 rounded hover:bg-gray-300">
            &#8592; Back to Admin Dashboard
        </a>
        <a href="/GreenBin/adminEditProfile" class="text-sm bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
            Edit Profile
        </a>
    </div>
</main>

</body>
</html>
