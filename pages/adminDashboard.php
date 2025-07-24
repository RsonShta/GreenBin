<?php
require $_SERVER['DOCUMENT_ROOT'] . '/GreenBin/backend/includes/auth.php';
requireRole(['superAdmin', 'admin']);

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
    <title><?= $lang === 'np' ? 'एडमिन ड्यासबोर्ड' : 'Admin Dashboard' ?> - GreenBin Nepal</title>
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

    <main class="max-w-7xl mx-auto p-6">
        <h1 class="text-3xl font-bold text-green-800 mb-6">
            <?= $lang === 'np' ? 'एडमिन ड्यासबोर्ड' : 'Admin Dashboard' ?>
        </h1>

        <!-- Filter Tabs -->
        <nav class="flex space-x-4 mb-6">
            <button data-status="all" class="filter-tab bg-green-600 text-white px-4 py-2 rounded shadow font-semibold">
                <?= $lang === 'np' ? 'सबै' : 'All' ?>
            </button>
            <button data-status="pending"
                class="filter-tab bg-yellow-500 text-white px-4 py-2 rounded shadow font-semibold">
                <?= $lang === 'np' ? 'प्रतीक्षा' : 'Pending' ?>
            </button>
            <button data-status="in-progress"
                class="filter-tab bg-blue-500 text-white px-4 py-2 rounded shadow font-semibold">
                <?= $lang === 'np' ? 'काम भइरहेको' : 'In Progress' ?>
            </button>
            <button data-status="resolved"
                class="filter-tab bg-green-800 text-white px-4 py-2 rounded shadow font-semibold">
                <?= $lang === 'np' ? 'समाधान' : 'Resolved' ?>
            </button>
        </nav>

        <!-- Reports Grid -->
        <section id="reportsContainer" class="grid md:grid-cols-2 lg:grid-cols-3 gap-6"></section>
        <p id="noReports" class="text-center text-gray-500 mt-8 hidden">
            <?= $lang === 'np' ? 'कुनै रिपोर्ट भेटिएन।' : 'No reports found.' ?>
        </p>
    </main>

    <!-- Report Detail Modal -->
    <div id="reportModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4">
        <div class="bg-white rounded-lg shadow-lg max-w-lg w-full p-6 relative">
            <button onclick="closeModal()"
                class="absolute top-2 right-2 text-gray-500 hover:text-gray-800 text-xl">&times;</button>

            <h3 class="text-xl font-bold text-green-700 mb-4">
                <?= $lang === 'np' ? 'रिपोर्ट विवरण' : 'Report Details' ?>
            </h3>
            <img id="modalImage" class="w-full h-48 object-cover rounded mb-4" alt="Report Image">

            <p class="text-sm text-gray-700 mb-2"><strong><?= $lang === 'np' ? 'शीर्षक' : 'Title' ?>:</strong>
                <span id="modalTitle"></span>
            </p>
            <p class="text-sm text-gray-700 mb-2"><strong><?= $lang === 'np' ? 'विवरण' : 'Description' ?>:</strong>
                <span id="modalDescription"></span>
            </p>
            <p class="text-sm text-gray-700 mb-2"><strong><?= $lang === 'np' ? 'प्रयोगकर्ता ID' : 'User ID' ?>:</strong>
                <span id="modalUser"></span>
            </p>
            <p class="text-sm text-gray-700 mb-4"><strong><?= $lang === 'np' ? 'स्थान' : 'Location' ?>:</strong>
                <span id="modalLocation"></span>
            </p>

            <label class="block text-sm font-semibold mb-1">
                <?= $lang === 'np' ? 'स्थिति अपडेट गर्नुहोस्' : 'Update Status' ?>
            </label>
            <select id="statusSelect" class="w-full border border-gray-300 rounded p-2 mb-4">
                <option value="pending"><?= $lang === 'np' ? 'प्रतीक्षा' : 'Pending' ?></option>
                <option value="in-progress"><?= $lang === 'np' ? 'काम भइरहेको' : 'In Progress' ?></option>
                <option value="resolved"><?= $lang === 'np' ? 'समाधान' : 'Resolved' ?></option>
            </select>

            <button onclick="updateStatus()"
                class="bg-green-700 hover:bg-green-800 text-white px-4 py-2 rounded w-full">
                <?= $lang === 'np' ? 'स्थिति अपडेट गर्नुहोस्' : 'Update Status' ?>
            </button>
        </div>
    </div>

    <script src="/GreenBin/frontend/admin/adminDashboard.js"></script>
</body>

</html>
