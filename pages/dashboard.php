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

// CO2 Reduction (optional, adjust if you don't have this column)
$stmt = $pdo->prepare("SELECT SUM(co2_reduction_kg) FROM reports WHERE user_id = ?");
$stmt->execute([$userId]);
$co2Reduction = $stmt->fetchColumn() ?: 0;

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
        <h2 class="text-xl font-semibold">Environmental Impact</h2>
        <p id="co2Reduction" class="text-3xl font-bold mt-2"><?= number_format($co2Reduction, 1) ?> kg</p>
        <p class="text-sm text-gray-600 mt-1">CO₂ reduction estimated</p>
      </div>
      <div class="bg-green-100 rounded p-4 shadow">
        <h2 class="text-xl font-semibold">Community Points</h2>
        <p id="communityPoints" class="text-3xl font-bold mt-2"><?= $communityPoints ?></p>
        <p class="text-sm text-gray-600 mt-1">Eco-warrior level</p>
      </div>
    </div>


    <!-- Navigation Tabs -->
    <nav class="flex justify-center mt-6 space-x-8 text-green-700 font-semibold text-lg">
      <a href="#" id="reportsTab" class="tab-link border-b-2 border-green-700 pb-1"><?= $lang === 'np' ? 'रिपोर्टहरू' : 'Reports' ?></a>
      <a href="#" id="analyticsTab" class="tab-link hover:underline"><?= $lang === 'np' ? 'विश्लेषण' : 'Analytics' ?></a>
      <a href="#" class="tab-link hover:underline"><?= $lang === 'np' ? 'समुदाय' : 'Community' ?></a>
      <a href="#" class="tab-link hover:underline"><?= $lang === 'np' ? 'उपलब्धिहरू' : 'Achievements' ?></a>
      <a href="#" class="tab-link hover:underline"><?= $lang === 'np' ? 'इको सुझावहरू' : 'Eco Tips' ?></a>
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

    <!-- Analytics Section -->
    <section id="analyticsSection" class="max-w-7xl mx-auto space-y-6 hidden">
      <!-- Analytics Header -->
      <div class="bg-white border rounded-lg shadow p-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-2">
          <?= $lang === 'np' ? 'विश्लेषण ड्यासबोर्ड' : 'Analytics Dashboard' ?>
        </h2>
        <p class="text-gray-600">
          <?= $lang === 'np' ? 'तपाईंको रिपोर्टिङ गतिविधि र प्रभावको विस्तृत दृश्य' : 'Detailed view of your reporting activity and impact' ?>
        </p>
      </div>

      <!-- Performance Metrics -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white p-6 rounded-lg shadow border-l-4 border-blue-500">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-600"><?= $lang === 'np' ? 'यस महिनाका रिपोर्टहरू' : 'This Month Reports' ?></p>
              <p id="currentMonthReports" class="text-2xl font-bold text-gray-900">0</p>
            </div>
            <div id="monthlyChange" class="text-sm font-semibold"></div>
          </div>
        </div>
        
        <div class="bg-white p-6 rounded-lg shadow border-l-4 border-green-500">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-600"><?= $lang === 'np' ? 'औसत प्रतिक्रिया समय' : 'Avg Response Time' ?></p>
              <p id="avgResponseTime" class="text-2xl font-bold text-gray-900">-</p>
            </div>
            <i class="fas fa-clock text-green-500 text-xl"></i>
          </div>
        </div>
        
        <div class="bg-white p-6 rounded-lg shadow border-l-4 border-yellow-500">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-600"><?= $lang === 'np' ? 'समाधान दर' : 'Resolution Rate' ?></p>
              <p id="analyticsResolutionRate" class="text-2xl font-bold text-gray-900">0%</p>
            </div>
            <i class="fas fa-chart-pie text-yellow-500 text-xl"></i>
          </div>
        </div>
        
        <div class="bg-white p-6 rounded-lg shadow border-l-4 border-purple-500">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-600"><?= $lang === 'np' ? 'कुल CO₂ प्रभाव' : 'Total CO₂ Impact' ?></p>
              <p id="totalCo2Impact" class="text-2xl font-bold text-gray-900">0 kg</p>
            </div>
            <i class="fas fa-leaf text-purple-500 text-xl"></i>
          </div>
        </div>
      </div>

      <!-- Charts Row -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Status Distribution Chart -->
        <div class="bg-white p-6 rounded-lg shadow">
          <h3 class="text-lg font-semibold mb-4">
            <?= $lang === 'np' ? 'स्थिति वितरण' : 'Status Distribution' ?>
          </h3>
          <div class="h-64 flex items-center justify-center">
            <canvas id="statusChart"></canvas>
          </div>
        </div>

        <!-- Monthly Trend Chart -->
        <div class="bg-white p-6 rounded-lg shadow">
          <h3 class="text-lg font-semibold mb-4">
            <?= $lang === 'np' ? 'मासिक प्रवृत्ति' : 'Monthly Trend' ?>
          </h3>
          <div class="h-64 flex items-center justify-center">
            <canvas id="monthlyChart"></canvas>
          </div>
        </div>
      </div>

      <!-- Weekly Activity Chart -->
      <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="text-lg font-semibold mb-4">
          <?= $lang === 'np' ? 'साप्ताहिक गतिविधि' : 'Weekly Activity' ?>
        </h3>
        <div class="h-64">
          <canvas id="weeklyChart"></canvas>
        </div>
      </div>

      <!-- Admin-only sections -->
      <div id="adminAnalytics" class="space-y-6 hidden">
        <!-- Top Locations -->
        <div class="bg-white p-6 rounded-lg shadow">
          <h3 class="text-lg font-semibold mb-4">
            <?= $lang === 'np' ? 'शीर्ष स्थानहरू' : 'Top Locations' ?>
          </h3>
          <div class="overflow-x-auto">
            <table class="w-full table-auto">
              <thead>
                <tr class="border-b">
                  <th class="text-left py-2"><?= $lang === 'np' ? 'स्थान' : 'Location' ?></th>
                  <th class="text-left py-2"><?= $lang === 'np' ? 'कुल रिपोर्टहरू' : 'Total Reports' ?></th>
                  <th class="text-left py-2"><?= $lang === 'np' ? 'समाधान भएका' : 'Resolved' ?></th>
                  <th class="text-left py-2"><?= $lang === 'np' ? 'दर' : 'Rate' ?></th>
                </tr>
              </thead>
              <tbody id="topLocationsTable">
                <!-- Populated by JavaScript -->
              </tbody>
            </table>
          </div>
        </div>

        <!-- User Activity -->
        <div class="bg-white p-6 rounded-lg shadow">
          <h3 class="text-lg font-semibold mb-4">
            <?= $lang === 'np' ? 'शीर्ष योगदानकर्ताहरू' : 'Top Contributors' ?>
          </h3>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" id="topContributors">
            <!-- Populated by JavaScript -->
          </div>
        </div>
      </div>

      <!-- Environmental Impact Details -->
      <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="text-lg font-semibold mb-4">
          <?= $lang === 'np' ? 'पर्यावरणीय प्रभाव विवरण' : 'Environmental Impact Details' ?>
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-center">
          <div class="p-4 bg-green-50 rounded-lg">
            <i class="fas fa-tree text-green-600 text-2xl mb-2"></i>
            <p class="text-sm text-gray-600"><?= $lang === 'np' ? 'कुल प्रभाव' : 'Total Impact' ?></p>
            <p id="environmentalImpactTotal" class="text-xl font-bold text-green-700">0 kg CO₂</p>
          </div>
          <div class="p-4 bg-blue-50 rounded-lg">
            <i class="fas fa-recycle text-blue-600 text-2xl mb-2"></i>
            <p class="text-sm text-gray-600"><?= $lang === 'np' ? 'रिसाइकल गरिएको' : 'Recycled' ?></p>
            <p id="recycledImpact" class="text-xl font-bold text-blue-700">0 kg CO₂</p>
          </div>
          <div class="p-4 bg-purple-50 rounded-lg">
            <i class="fas fa-seedling text-purple-600 text-2xl mb-2"></i>
            <p class="text-sm text-gray-600"><?= $lang === 'np' ? 'बचत गरिएको' : 'Saved' ?></p>
            <p id="savedImpact" class="text-xl font-bold text-purple-700">0 kg CO₂</p>
          </div>
        </div>
      </div>
    </section>

  </main>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const locationPermissionMessage = document.getElementById('location-permission-message');
    const locationInput = document.getElementById('location');

    if (navigator.geolocation) {
        locationPermissionMessage.textContent = '<?= $lang === 'np' ? 'तपाईंको स्थान स्वचालित रूपमा पत्ता लगाउन अनुमति दिनुहोस्। तपाईं पछि यसलाई परिवर्तन गर्न सक्नुहुन्छ।' : 'Please allow location access to automatically detect your location. You can change it later.' ?>';

        navigator.geolocation.getCurrentPosition(
            (position) => {
                const { latitude, longitude } = position.coords;
                const coords = `${latitude},${longitude}`;
                locationInput.value = coords;
                sessionStorage.setItem('user_location', coords);
                locationPermissionMessage.textContent = '<?= $lang === 'np' ? 'स्थान सफलतापूर्वक पत्ता लाग्यो।' : 'Location successfully detected.' ?>';
            },
            (error) => {
                locationPermissionMessage.textContent = '<?= $lang === 'np' ? 'स्थान पहुँच अस्वीकार गरियो। कृपया म्यानुअल रूपमा स्थान प्रविष्ट गर्नुहोस्।' : 'Location access denied. Please enter location manually.' ?>';
                console.error('Geolocation error:', error);
            }
        );
    } else {
        locationPermissionMessage.textContent = '<?= $lang === 'np' ? 'तपाईंको ब्राउजरले जियोलोकेशन समर्थन गर्दैन।' : 'Your browser does not support geolocation.' ?>';
    }
});
</script>
<?php require_once 'includes/user_footer.php'; ?>
