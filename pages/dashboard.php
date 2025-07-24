<?php require_once 'includes/user_header.php'; ?>
<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/GreenBin/backend/includes/auth.php';
requireRole(['user']);
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

// Community Points (example: resolved reports * 10 points)
$communityPoints = $resolvedCount * 10;

// Resolution Rate
$resolutionRate = $totalReports > 0 ? round(($resolvedCount / $totalReports) * 100, 1) : 0.0;

?>

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
      <h2 class="text-xl font-semibold">Total Reports</h2>
      <p id="totalReports" class="text-3xl font-bold mt-2"><?= $totalReports ?></p>
      <p id="statusCounts" class="text-sm text-gray-600 mt-1">
        <?= "{$pendingCount} pending, {$inProgressCount} in progress" ?>
      </p>
    </div>
    <div class="bg-green-100 rounded p-4 shadow">
      <h2 class="text-xl font-semibold">Resolution Rate</h2>
      <p id="resolutionRate" class="text-3xl font-bold mt-2"><?= $resolutionRate ?>%</p>
      <p id="resolvedCount" class="text-sm text-gray-600 mt-1"><?= $resolvedCount ?> resolved</p>
    </div>
    <div class="bg-green-100 rounded p-4 shadow">
      <h2 class="text-xl font-semibold">Community Points</h2>
      <p id="communityPoints" class="text-3xl font-bold mt-2"><?= $communityPoints ?></p>
      <p class="text-sm text-gray-600 mt-1">Eco-warrior level</p>
    </div>
  </div>


  <!-- Navigation Tabs -->
  <nav class="flex justify-center mt-6 space-x-8 text-green-700 font-semibold text-lg">
    <a href="#" id="reportsTab"
      class="tab-link border-b-2 border-green-700 pb-1"><?= $lang === 'np' ? 'रिपोर्टहरू' : ' ' ?></a>
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

      <input type="hidden" id="location" name="location" />
      <div id="location-permission-message" class="text-sm text-gray-600"></div>

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
        <input type="file" id="fileInput" name="photo" style="display:none" accept="image/png, image/jpeg, image/gif" />
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
  <section id="reportsSection" class="max-w-50xl mx-auto bg-white border rounded-lg shadow p-6">
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

<?php require_once 'includes/user_footer.php'; ?>
