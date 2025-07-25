<?php
require $_SERVER['DOCUMENT_ROOT'] . '/GreenBin/backend/includes/auth.php';
requireRole(['superAdmin']); // Only superadmins can access this page

require $_SERVER['DOCUMENT_ROOT'] . '/GreenBin/backend/includes/db.php';

// Language Switch
if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'np'])) {
    $_SESSION['lang'] = $_GET['lang'];
}
$lang = $_SESSION['lang'] ?? 'en';

// --- User Management Logic from manageUsers.php ---
// Generate CSRF token if it doesn't exist
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// Fetch all users except superAdmin
$users = $pdo->query("SELECT id, first_name, email_id, phone_number, role FROM users WHERE role != 'superAdmin'")->fetchAll(PDO::FETCH_ASSOC);
// --- End User Management Logic ---
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
                <h1 class="text-lg font-bold text-green-800 leading-tight">हरित नेपाल</h1>
                <span class="text-xs text-gray-500">GreenBin Nepal</span>
            </div>
        </div>
        <div class="flex items-center gap-4 text-sm">
            <a href="/GreenBin/backend/logout.php"
                class="border px-3 py-1 rounded-md text-gray-800 hover:bg-gray-100 flex items-center gap-1 text-sm transition">
                <?= $lang === 'np' ? 'लग आउट' : 'Logout' ?>
            </a>
        </div>
    </header>

    <main class="max-w-7xl mx-auto p-6">
        <h1 class="text-3xl font-bold text-green-800 mb-6">
            <? 'Superadmin Dashboard' ?>
        </h1>

        <!-- Superadmin Specific Content -->
        <section class="bg-white p-6 rounded-lg shadow mb-6">
            <h2 class="text-xl font-bold text-green-700 mb-4">System Overview</h2>
            <p class="text-gray-700 mb-2">Welcome to the Superadmin Dashboard.</p>
        </section>

        <!-- User Management Section -->
        <section class="bg-white p-6 rounded-lg shadow mb-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold text-green-700">User Management</h2>
                <div>
                    <input type="text" id="userSearch" placeholder="Search users..."
                        class="p-2 border rounded focus:outline-none focus:ring-2 focus:ring-green-500">
                    <button id="add-user-btn"
                        class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 ml-2">Add
                        Admin</button>
                </div>
            </div>

            <table class="min-w-full bg-white shadow rounded">
                <thead class="bg-gray-100 text-left">
                    <tr>
                        <th class="p-3">Name</th>
                        <th class="p-3">Email</th>
                        <th class="p-3">Phone</th>
                        <th class="p-3">Role</th>
                        <th class="p-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr data-user-id="<?= $user['id'] ?>" class="border-t hover:bg-gray-50">
                            <td class="p-3"><?= htmlspecialchars($user['first_name']) ?></td>
                            <td class="p-3"><?= htmlspecialchars($user['email_id']) ?></td>
                            <td class="p-3"><?= htmlspecialchars($user['phone_number']) ?></td>
                            <td class="p-3">
                                <select class="role-select border rounded p-1" data-current-role="<?= $user['role'] ?>">
                                    <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>User</option>
                                    <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                </select>
                            </td>
                            <td class="p-3">
                                <button
                                    class="delete-btn bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>

        <!-- Add User Modal -->
        <div id="add-user-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3 text-center">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Add New Admin</h3>
                    <div class="mt-2 px-7 py-3">
                        <form id="add-user-form" novalidate>
                            <div class="mb-2 text-left">
                                <input type="text" name="first_name" placeholder="First Name"
                                    class="w-full p-2 border rounded" required>
                                <span class="text-red-500 text-xs error-message"></span>
                            </div>
                            <div class="mb-2 text-left">
                                <input type="email" name="email" placeholder="Email" class="w-full p-2 border rounded"
                                    required>
                                <span class="text-red-500 text-xs error-message"></span>
                            </div>
                            <div class="mb-2 text-left">
                                <input type="text" name="phone_number" placeholder="Phone Number"
                                    class="w-full p-2 border rounded" required>
                                <span class="text-red-500 text-xs error-message"></span>
                            </div>
                            <div class="mb-2 text-left">
                                <input type="password" name="password" placeholder="Password"
                                    class="w-full p-2 border rounded" required>
                                <span class="text-red-500 text-xs error-message"></span>
                            </div>
                            <div class="mb-2 text-left">
                                <input type="text" name="ward" placeholder="Ward" class="w-full p-2 border rounded"
                                    required>
                                <span class="text-red-500 text-xs error-message"></span>
                            </div>
                            <div class="mb-2 text-left">
                                <input type="text" name="nagarpalika" placeholder="Nagarpalika"
                                    class="w-full p-2 border rounded" required>
                                <span class="text-red-500 text-xs error-message"></span>
                            </div>
                            <div class="mb-2 text-left">
                                <input type="text" name="address" placeholder="Address"
                                    class="w-full p-2 border rounded" required>
                                <span class="text-red-500 text-xs error-message"></span>
                            </div>
                            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                        </form>
                    </div>
                    <div class="items-center px-4 py-3">
                        <button id="cancel-add-user"
                            class="px-4 py-2 bg-gray-500 text-white text-base font-medium rounded-md w-auto shadow-sm hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-300">
                            Cancel
                        </button>
                        <button id="submit-add-user"
                            class="px-4 py-2 bg-green-600 text-white text-base font-medium rounded-md w-auto shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-300">
                            Add
                        </button>
                    </div>
                </div>
            </div>
        </div>

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

    <script src="/GreenBin/frontend/superAdmin/superAdmin.js"></script>
</body>

</html>