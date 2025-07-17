<?php
require $_SERVER['DOCUMENT_ROOT'] . '/GreenBin/backend/includes/auth.php';
requireRole(['superAdmin', 'admin', 'user']);

$userName = htmlspecialchars($_SESSION['user_name']);
$userRole = $_SESSION['user_role'];

require $_SERVER['DOCUMENT_ROOT'] . '/GreenBin/backend/includes/db.php';

// 🌐 Language Switch
if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'np'])) {
  $_SESSION['lang'] = $_GET['lang'];
}
$lang = $_SESSION['lang'] ?? 'en';

// ==================== ✅ DASHBOARD STATS QUERIES ====================
$userId = $_SESSION['user_id'] ?? 0;

// Total Reports
$stmt = $pdo->prepare("SELECT COUNT(*) FROM reports WHERE user_id = ?");
$stmt->execute([$userId]);
$totalReports = $stmt->fetchColumn() ?: 0;

// Pending, In Progress, Resolved
$stmt = $pdo->prepare("
  SELECT 
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending,
    SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) AS in_progress,
    SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) AS resolved
  FROM reports WHERE user_id = ?
");
$stmt->execute([$userId]);
$statusCounts = $stmt->fetch(PDO::FETCH_ASSOC);
$pendingCount = $statusCounts['pending'] ?? 0;
$inProgressCount = $statusCounts['in_progress'] ?? 0;
$resolvedCount = $statusCounts['resolved'] ?? 0;

// CO2 Reduction (optional, adjust if you don't have this column)
$stmt = $pdo->prepare("SELECT SUM(co2_reduction_kg) FROM reports WHERE user_id = ?");
$stmt->execute([$userId]);
$co2Reduction = $stmt->fetchColumn() ?: 0;

// Community Points (example: resolved reports * 10 points)
$communityPoints = $resolvedCount * 10;

// Resolution Rate
$resolutionRate = $totalReports > 0 ? round(($resolvedCount / $totalReports) * 100, 1) : 0.0;
?>

<!DOCTYPE html>
<html lang="<?= $lang ?>">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= $lang === 'np' ? 'ड्यासबोर्ड - हरित नेपाल' : 'Dashboard - हरित नेपाल' ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet" />
  <link href="/GreenBin/frontend/dashboard/dashboard.css" rel="stylesheet">
  <script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: {
            inter: ['Inter', 'sans-serif']
          },
          colors: {
            primary: '#2e7d32',
            dark: '#1b5e20',
            light: '#f0fdf4'
          }
        }
      }
    }
  </script>
</head>

<body class="bg-gray-100 min-h-screen m-0 p-0">

  <!-- Header -->
  <header class="bg-white shadow-sm border-b border-gray-200 py-4 px-6 flex justify-between items-center">
    <!-- Logo & Name -->
    <div class="flex items-center gap-3">
      <img src="/GreenBin/frontend/img/mountain.png" class="w-8 h-8" alt="Logo" />
      <div>
        <h1 class="text-lg font-bold text-green-700 leading-tight">हरित नेपाल</h1>
        <span class="text-xs text-gray-500">GreenBin Nepal</span>
      </div>
    </div>

    <!-- Right Section: Language | Profile | Logout -->
    <div class="flex items-center gap-4 text-sm">
      <!-- 🌐 Language Switch -->
      <a href="?lang=<?= $lang === 'en' ? 'np' : 'en' ?>"
        class="text-xs px-2 py-1 border rounded hover:bg-gray-100 transition">
        🌐 <?= $lang === 'en' ? 'नेपाली' : 'English' ?>
      </a>

      <!-- 👤 Profile View -->
      <a href="/GreenBin/profile" class="flex items-center gap-1 text-gray-700 hover:text-green-700 transition">
        <i class="fas fa-user-circle"></i> <?= $lang === 'np' ? 'प्रोफाइल' : 'Profile' ?>
      </a>

      <!-- 🚪 Logout -->
      <a href="/GreenBin/backend/logout.php"
        class="border px-3 py-1 rounded-md text-gray-800 hover:bg-gray-100 flex items-center gap-1 text-sm transition">
        <i class="fas fa-sign-out-alt"></i> <?= $lang === 'np' ? 'लग आउट' : 'Logout' ?>
      </a>
    </div>
  </header>

  <!-- Main Content -->
  <main class="max-w-[100%] mx-auto p-6">

    <!-- ✅ Dashboard Title + New Report Button -->
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-3xl font-bold text-green-800">
        <?= $lang === 'np' ? 'ड्यासबोर्ड' : 'Dashboard' ?>
      </h1>
      <button id="showReportFormBtn"
        class="bg-green-700 hover:bg-green-800 text-white px-5 py-2 rounded shadow flex items-center gap-2 font-semibold transition">
        <i class="fas fa-plus"></i> <?= $lang === 'np' ? 'नयाँ रिपोर्ट' : 'New Report' ?>
      </button>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6 text-center text-gray-800">
      <div class="bg-green-100 rounded p-4 shadow">
        <h2 class="text-xl font-semibold"><?= $lang === 'np' ? 'कुल रिपोर्टहरू' : 'Total Reports' ?></h2>
        <p class="text-3xl font-bold mt-2"><?= $totalReports ?></p>
        <p class="text-sm text-gray-600 mt-1">
          <?= $lang === 'np' ? "{$pendingCount} पेंडिङ, {$inProgressCount} प्रक्रियामा" : "{$pendingCount} pending, {$inProgressCount} in progress" ?>
        </p>
      </div>
      <div class="bg-green-100 rounded p-4 shadow">
        <h2 class="text-xl font-semibold"><?= $lang === 'np' ? 'समाधान दर' : 'Resolution Rate' ?></h2>
        <p class="text-3xl font-bold mt-2"><?= $resolutionRate ?>%</p>
        <p class="text-sm text-gray-600 mt-1">
          <?= $lang === 'np' ? "{$resolvedCount} समाधान भयो" : "{$resolvedCount} resolved" ?></p>
      </div>
      <div class="bg-green-100 rounded p-4 shadow">
        <h2 class="text-xl font-semibold"><?= $lang === 'np' ? 'पर्यावरण प्रभाव' : 'Environmental Impact' ?></h2>
        <p class="text-3xl font-bold mt-2"><?= number_format($co2Reduction, 1) ?> kg</p>
        <p class="text-sm text-gray-600 mt-1"><?= $lang === 'np' ? 'CO₂ कमी अनुमानित' : 'CO₂ reduction estimated' ?></p>
      </div>
      <div class="bg-green-100 rounded p-4 shadow">
        <h2 class="text-xl font-semibold"><?= $lang === 'np' ? 'समुदाय अंकहरू' : 'Community Points' ?></h2>
        <p class="text-3xl font-bold mt-2"><?= $communityPoints ?></p>
        <p class="text-sm text-gray-600 mt-1"><?= $lang === 'np' ? 'इको-वारियर स्तर' : 'Eco-warrior level' ?></p>
      </div>
    </div>

    <!-- Navigation Tabs -->
    <nav class="flex justify-center mt-6 space-x-8 text-green-700 font-semibold text-lg">
      <a href="#" class="hover:underline"><?= $lang === 'np' ? 'रिपोर्टहरू' : 'Reports' ?></a>
      <a href="#" class="hover:underline"><?= $lang === 'np' ? 'विश्लेषण' : 'Analytics' ?></a>
      <a href="#" class="hover:underline"><?= $lang === 'np' ? 'समुदाय' : 'Community' ?></a>
      <a href="#" class="hover:underline"><?= $lang === 'np' ? 'उपलब्धिहरू' : 'Achievements' ?></a>
      <a href="#" class="hover:underline"><?= $lang === 'np' ? 'इको सुझावहरू' : 'Eco Tips' ?></a>
    </nav>

    <!-- Report Submission Form (Initially Hidden) -->
    <section id="reportFormSection" class="bg-white p-6 rounded-lg shadow-md max-w-70xl mx-auto mb-10 hidden">
      <h3 class="text-xl font-bold mb-4">
        <?= $lang === 'np' ? 'पर्यावरण रिपोर्ट पठाउनुहोस्' : 'Submit Environmental Report' ?>
      </h3>

      <form class="form-container space-y-4" enctype="multipart/form-data" method="POST"
        action="/GreenBin/backend/reportSubmit.php" novalidate>
        <div>
          <label for="reportTitle" class="block text-sm font-medium mb-1">
            <?= $lang === 'np' ? 'रिपोर्ट शीर्षक' : 'Report Title' ?>
          </label>
          <input id="reportTitle" name="reportTitle" type="text"
            placeholder="<?= $lang === 'np' ? 'उदाहरण: ओक सडकमा अवैध फोहोर फाल्नु' : 'e.g., Illegal dumping on Oak Street' ?>"
            required
            class="w-full border border-gray-300 p-2 rounded-md focus:ring focus:ring-green-200 focus:outline-none" />
        </div>

        <div>
          <label for="description" class="block text-sm font-medium mb-1">
            <?= $lang === 'np' ? 'विवरण' : 'Description' ?>
          </label>
          <textarea id="description" name="description"
            placeholder="<?= $lang === 'np' ? 'विवरण लेख्नुहोस्' : 'Description' ?>" required
            class="w-full border border-gray-300 p-2 rounded-md focus:ring focus:ring-green-200 focus:outline-none h-32"></textarea>
        </div>

        <div>
          <label class="block text-sm font-medium mb-1">
            <?= $lang === 'np' ? 'फोटो अपलोड गर्नुहोस् (वैकल्पिक)' : 'Upload Photo (Optional)' ?>
          </label>
          <div id="uploadArea" tabindex="0" role="button" aria-label="Upload photo"
            class="upload-area cursor-pointer border border-dashed border-green-400 rounded-md p-4 text-center hover:bg-green-50 focus:outline-none focus:ring-2 focus:ring-green-400">
            <i class="fas fa-cloud-upload-alt text-3xl text-green-600 mb-2"></i>
            <p><?= $lang === 'np' ? 'अपलोड गर्न क्लिक गर्नुहोस् वा तान्नुहोस्' : 'Click to upload or drag and drop' ?>
            </p>
            <p class="file-info text-xs text-gray-500">PNG, JPG, GIF up to 5MB</p>
          </div>
          <input type="file" id="fileInput" name="photo" style="display:none"
            accept="image/png, image/jpeg, image/gif" />
        </div>

        <div class="flex gap-4 pt-4">
          <button type="submit"
            class="bg-green-700 hover:bg-green-800 text-white flex-1 py-2 rounded-md flex justify-center items-center gap-2">
            <i class="fas fa-upload"></i> <?= $lang === 'np' ? 'पठाउनुहोस्' : 'Submit' ?>
          </button>
          <button type="button" id="cancelReportFormBtn"
            class="bg-gray-300 hover:bg-gray-400 text-gray-800 flex-1 py-2 rounded-md">
            <?= $lang === 'np' ? 'रद्द गर्नुहोस्' : 'Cancel' ?>
          </button>
        </div>
      </form>
    </section>

    <!-- Your Reports Section -->
    <section class="max-w-50xl mx-auto bg-white border rounded-lg shadow p-6">
      <h2 class="text-xl font-semibold text-gray-800 mb-4">
        <?= $lang === 'np' ? 'तपाईंको रिपोर्टहरू' : 'Your Reports' ?>
      </h2>
      <p class="text-sm text-gray-500 mb-6">
        <?= $lang === 'np' ? 'तपाईंले पठाएका रिपोर्टहरू हेर्नुहोस्।' : "View the environmental reports you've submitted." ?>
      </p>

      <!-- Reports container (will be populated by JavaScript) -->
      <div class="reports-list grid gap-4" id="reportsList"></div>

      <div class="no-reports text-center text-gray-500 py-8 border border-dashed rounded-md" style="display:none;"
        id="noReports">
        <p class="text-sm mb-3">
          <?= $lang === 'np' ? 'अहिलेसम्म कुनै रिपोर्ट पठाइएको छैन।' : 'No reports submitted yet.' ?>
        </p>
        <button id="firstReportBtn"
          class="btn-primary px-4 py-2 text-sm bg-green-600 text-white rounded hover:bg-green-700 transition">
          <?= $lang === 'np' ? 'पहिलो रिपोर्ट पठाउनुहोस्' : 'Submit Your First Report' ?>
        </button>
      </div>
    </section>

  </main>

  <script src="https://kit.fontawesome.com/3f471bb5a5.js" crossorigin="anonymous"></script>
  <script src="/GreenBin/frontend/dashboard/dashboard.js" defer></script>
  <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_GOOGLE_MAPS_API_KEY&callback=initMap" async
    defer></script>

  <!-- Edit Report Modal -->
  <div id="editModal" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50 hidden">
    <div class="bg-white p-6 rounded-md w-full max-w-lg shadow-lg relative">
      <button onclick="closeEditModal()" class="absolute top-2 right-2 text-gray-600 text-xl">&times;</button>
      <h3 class="text-xl font-bold mb-4"><?= $lang === 'np' ? 'रिपोर्ट सम्पादन गर्नुहोस्' : 'Edit Report' ?></h3>
      <form id="editReportForm" class="space-y-4" enctype="multipart/form-data">
        <input type="hidden" name="reportId" id="editReportId" />

        <div>
          <label for="editTitle" class="block text-sm font-medium mb-1">
            <?= $lang === 'np' ? 'शीर्षक' : 'Title' ?>
          </label>
          <input type="text" id="editTitle" name="title" required
            class="w-full border border-gray-300 p-2 rounded-md" />
        </div>

        <div>
          <label for="editDescription" class="block text-sm font-medium mb-1">
            <?= $lang === 'np' ? 'विवरण' : 'Description' ?>
          </label>
          <textarea id="editDescription" name="description" required rows="4"
            class="w-full border border-gray-300 p-2 rounded-md"></textarea>
        </div>

        <div>
          <label for="editLocation" class="block text-sm font-medium mb-1">
            <?= $lang === 'np' ? 'स्थान' : 'Location' ?>
          </label>
          <input type="text" id="editLocation" name="location" class="w-full border border-gray-300 p-2 rounded-md" />
        </div>

        <div>
          <label class="block text-sm font-medium mb-1">
            <?= $lang === 'np' ? 'नयाँ फोटो (वैकल्पिक)' : 'New Photo (optional)' ?>
          </label>
          <input type="file" name="photo" id="editPhoto" accept="image/*"
            class="w-full border border-dashed p-2 rounded-md" />
        </div>

        <div class="text-right">
          <button type="submit" class="bg-green-700 hover:bg-green-800 text-white px-4 py-2 rounded-md">
            <?= $lang === 'np' ? 'परिवर्तनहरू सुरक्षित गर्नुहोस्' : 'Save Changes' ?>
          </button>
        </div>
        <p id="editError" class="text-sm text-red-600 mt-2 hidden"></p>
      </form>
    </div>
  </div>

</body>

</html>