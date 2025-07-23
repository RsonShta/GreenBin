<?php
require $_SERVER['DOCUMENT_ROOT'] . '/GreenBin/backend/includes/auth.php';
requireRole(['superadmin']); // Only superadmins can access this page

require $_SERVER['DOCUMENT_ROOT'] . '/GreenBin/backend/includes/db.php';

// Language Switch
if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'np'])) {
    $_SESSION['lang'] = $_GET['lang'];
}
$lang = $_SESSION['lang'] ?? 'en';
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $lang === 'np' ? 'सुपरएडमिन ड्यासबोर्ड' : 'Superadmin Dashboard' ?> - GreenBin Nepal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet" />
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { inter: ['Inter', 'sans-serif'] },
                    colors: {
                        primary: '#1976d2', // Example color for superadmin
                        dark: '#1565c0',
                        light: '#e3f2fd'
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
                <h1 class="text-lg font-bold text-blue-800 leading-tight">हरित नेपाल</h1>
                <span class="text-xs text-gray-500">GreenBin Nepal</span>
            </div>
        </div>
        <div class="flex items-center gap-4 text-sm">
            <a href="?lang=<?= $lang === 'en' ? 'np' : 'en' ?>"
                class="text-xs px-2 py-1 border rounded hover:bg-gray-100 transition">
                <?= $lang === 'en' ? 'नेपाली' : 'English' ?>
            </a>
            <a href="/GreenBin/superAdminProfile" class="text-gray-700 hover:text-blue-700 transition">
                <?= $lang === 'np' ? 'प्रोफाइल' : 'Profile' ?>
            </a>
            <a href="/GreenBin/backend/logout.php"
                class="border px-3 py-1 rounded-md text-gray-800 hover:bg-gray-100 flex items-center gap-1 text-sm transition">
                <?= $lang === 'np' ? 'लग आउट' : 'Logout' ?>
            </a>
        </div>
    </header>

    <main class="max-w-7xl mx-auto p-6">
        <h1 class="text-3xl font-bold text-blue-800 mb-6">
            <?= $lang === 'np' ? 'सुपरएडमिन ड्यासबोर्ड' : 'Superadmin Dashboard' ?>
        </h1>

        <!-- Superadmin Specific Content -->
        <section class="bg-white p-6 rounded-lg shadow mb-6">
            <h2 class="text-xl font-bold text-blue-700 mb-4">User Management</h2>
            <p class="text-gray-700 mb-2">Manage user roles and permissions here.</p>
            <a href="/GreenBin/superadmin/manageUsers" class="text-blue-600 hover:underline">Go to User Management</a>
        </section>

        <section class="bg-white p-6 rounded-lg shadow mb-6">
            <h2 class="text-xl font-bold text-blue-700 mb-4">System Settings</h2>
            <p class="text-gray-700 mb-2">Configure system-wide settings.</p>
            <a href="/GreenBin/superadmin/settings" class="text-blue-600 hover:underline">Go to System Settings</a>
        </section>

        <!-- Placeholder for Admin Dashboard Link if needed -->
        <?php if ($_SESSION['user_role'] === 'superadmin'): ?>
        <section class="bg-white p-6 rounded-lg shadow mb-6">
            <h2 class="text-xl font-bold text-green-700 mb-4">Admin Area</h2>
            <p class="text-gray-700 mb-2">Access the admin dashboard.</p>
            <a href="/GreenBin/adminDashboard" class="text-green-600 hover:underline">Go to Admin Dashboard</a>
        </section>
        <?php endif; ?>

    </main>

    <footer class="text-center text-gray-500 text-sm mt-8 mb-4">
        &copy; <?= date('Y') ?> GreenBin Nepal. All rights reserved.
    </footer>

</body>

</html>
