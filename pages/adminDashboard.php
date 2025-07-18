<?php
require $_SERVER['DOCUMENT_ROOT'] . '/GreenBin/backend/includes/auth.php';
requireRole(['superAdmin', 'admin']); // Only admins allowed

require $_SERVER['DOCUMENT_ROOT'] . '/GreenBin/backend/includes/db.php';

// Language support
if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'np'])) {
    $_SESSION['lang'] = $_GET['lang'];
}
$lang = $_SESSION['lang'] ?? 'en';

// Fetch some stats
// Total users
$stmt = $pdo->query("SELECT COUNT(*) FROM users");
$totalUsers = $stmt->fetchColumn() ?: 0;

// Total reports
$stmt = $pdo->query("SELECT COUNT(*) FROM reports");
$totalReports = $stmt->fetchColumn() ?: 0;

// Pending reports
$stmt = $pdo->prepare("SELECT COUNT(*) FROM reports WHERE status = ?");
$stmt->execute(['pending']);
$pendingReports = $stmt->fetchColumn() ?: 0;

// In-progress reports
$stmt = $pdo->prepare("SELECT COUNT(*) FROM reports WHERE status = ?");
$stmt->execute(['in_progress']);
$inProgressReports = $stmt->fetchColumn() ?: 0;

// Resolved reports
$stmt = $pdo->prepare("SELECT COUNT(*) FROM reports WHERE status = ?");
$stmt->execute(['resolved']);
$resolvedReports = $stmt->fetchColumn() ?: 0;

// Fetch recent users (limit 5) — use actual column names
$stmt = $pdo->query("SELECT id, first_name, email_id, role FROM users ORDER BY id DESC LIMIT 5");
$recentUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch recent reports (limit 5)
$stmt = $pdo->query("SELECT report_id, title, status, user_id FROM reports ORDER BY report_id DESC LIMIT 5");
$recentReports = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang) ?>">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?= $lang === 'np' ? 'एडमिन ड्यासबोर्ड' : 'Admin Dashboard' ?> - हरित नेपाल</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet" />
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="bg-gray-100 min-h-screen">

    <header class="bg-white shadow p-4 flex justify-between items-center">
        <div class="flex items-center gap-3">
            <img src="/GreenBin/frontend/img/mountain.png" alt="Logo" class="w-8 h-8" />
            <h1 class="text-green-700 font-bold text-lg"><?= $lang === 'np' ? 'हरित नेपाल' : 'GreenBin Nepal' ?></h1>
        </div>

        <nav class="flex items-center gap-4 text-sm text-gray-700">
            <a href="?lang=<?= $lang === 'en' ? 'np' : 'en' ?>" class="hover:underline">
                <?= $lang === 'en' ? 'नेपाली' : 'English' ?>
            </a>
            <a href="/GreenBin/backend/logout.php" class="hover:text-red-600">Logout</a>
        </nav>
    </header>

    <main class="max-w-7xl mx-auto p-6">

        <h2 class="text-3xl font-bold text-green-800 mb-6">
            <?= $lang === 'np' ? 'एडमिन ड्यासबोर्ड' : 'Admin Dashboard' ?>
        </h2>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-green-100 rounded p-4 shadow text-center">
                <h3 class="font-semibold text-lg mb-2"><?= $lang === 'np' ? 'कुल प्रयोगकर्ता' : 'Total Users' ?></h3>
                <p class="text-3xl font-bold text-green-700"><?= $totalUsers ?></p>
            </div>
            <div class="bg-green-100 rounded p-4 shadow text-center">
                <h3 class="font-semibold text-lg mb-2"><?= $lang === 'np' ? 'कुल रिपोर्टहरू' : 'Total Reports' ?></h3>
                <p class="text-3xl font-bold text-green-700"><?= $totalReports ?></p>
            </div>
            <div class="bg-yellow-100 rounded p-4 shadow text-center">
                <h3 class="font-semibold text-lg mb-2">
                    <?= $lang === 'np' ? 'प्रतीक्षा रिपोर्टहरू' : 'Pending Reports' ?>
                </h3>
                <p class="text-3xl font-bold text-yellow-700"><?= $pendingReports ?></p>
            </div>
            <div class="bg-blue-100 rounded p-4 shadow text-center">
                <h3 class="font-semibold text-lg mb-2">
                    <?= $lang === 'np' ? 'समाधान भएका रिपोर्टहरू' : 'Resolved Reports' ?>
                </h3>
                <p class="text-3xl font-bold text-blue-700"><?= $resolvedReports ?></p>
            </div>
        </div>

        <!-- Recent Users -->
        <section class="mb-10 bg-white rounded shadow p-6">
            <h3 class="text-xl font-semibold mb-4"><?= $lang === 'np' ? 'हालसालै थपिएका प्रयोगकर्ता' : 'Recent Users' ?>
            </h3>
            <?php if (count($recentUsers) > 0): ?>
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-gray-300">
                            <th class="py-2 px-4"><?= $lang === 'np' ? 'ID' : 'ID' ?></th>
                            <th class="py-2 px-4"><?= $lang === 'np' ? 'नाम' : 'Name' ?></th>
                            <th class="py-2 px-4"><?= $lang === 'np' ? 'इमेल' : 'Email' ?></th>
                            <th class="py-2 px-4"><?= $lang === 'np' ? 'भूमिका' : 'Role' ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentUsers as $user): ?>
                            <tr class="border-b border-gray-200 hover:bg-gray-50">
                                <td class="py-2 px-4"><?= htmlspecialchars($user['id']) ?></td>
                                <td class="py-2 px-4"><?= htmlspecialchars($user['first_name']) ?></td>
                                <td class="py-2 px-4"><?= htmlspecialchars($user['email_id']) ?></td>
                                <td class="py-2 px-4 capitalize"><?= htmlspecialchars($user['role']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p><?= $lang === 'np' ? 'कुनै प्रयोगकर्ता भेटिएन।' : 'No users found.' ?></p>
            <?php endif; ?>
        </section>

        <!-- Recent Reports -->
        <section class="mb-10 bg-white rounded shadow p-6">
            <h3 class="text-xl font-semibold mb-4"><?= $lang === 'np' ? 'हालसालै रिपोर्टहरू' : 'Recent Reports' ?></h3>
            <?php if (count($recentReports) > 0): ?>
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-gray-300">
                            <th class="py-2 px-4"><?= $lang === 'np' ? 'ID' : 'ID' ?></th>
                            <th class="py-2 px-4"><?= $lang === 'np' ? 'शीर्षक' : 'Title' ?></th>
                            <th class="py-2 px-4"><?= $lang === 'np' ? 'स्थिति' : 'Status' ?></th>
                            <th class="py-2 px-4"><?= $lang === 'np' ? 'प्रयोगकर्ता आईडी' : 'User ID' ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentReports as $report): ?>
                            <tr class="border-b border-gray-200 hover:bg-gray-50">
                                <td class="py-2 px-4"><?= htmlspecialchars($report['report_id']) ?></td>
                                <td class="py-2 px-4"><?= htmlspecialchars($report['title']) ?></td>
                                <td class="py-2 px-4 capitalize"><?= htmlspecialchars($report['status']) ?></td>
                                <td class="py-2 px-4"><?= htmlspecialchars($report['user_id']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p><?= $lang === 'np' ? 'कुनै रिपोर्ट भेटिएन।' : 'No reports found.' ?></p>
            <?php endif; ?>
        </section>

    </main>

</body>

</html>