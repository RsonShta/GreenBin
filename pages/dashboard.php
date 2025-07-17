<?php
require $_SERVER['DOCUMENT_ROOT'] . '/GreenBin/backend/includes/auth.php';
requireRole(['superAdmin', 'admin', 'user']);

$userName = htmlspecialchars($_SESSION['user_name']);
$userRole = $_SESSION['user_role'];

require $_SERVER['DOCUMENT_ROOT'] . '/GreenBin/backend/includes/db.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Dashboard - हरित नेपाल</title>
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

<body class="font-inter bg-gradient-to-br from-blue-50 via-white to-green-50 min-h-screen">

  <!-- Header -->
  <header class="bg-white shadow-sm border-b border-gray-200 py-4 px-6 flex justify-between items-center">
    <div class="flex items-center gap-3">
      <img src="/GreenBin/frontend/img/mountain.png" class="w-8 h-8" alt="Logo" />
      <div>
        <h1 class="text-lg font-bold text-green-700 leading-tight">हरित नेपाल</h1>
        <span class="text-xs text-gray-500">GreenBin Nepal</span>
      </div>
    </div>
    <div class="flex items-center gap-6 text-sm">
      <span class="flex items-center gap-1 text-gray-600"><i class="fas fa-globe"></i> नेपाली</span>
      <span class="flex items-center gap-1 text-gray-600">
        <i class="fas fa-user"></i> <?= $userName ?>
      </span>
      <a href="/GreenBin/backend/logout.php"
        class="border px-3 py-1 rounded-md text-gray-800 hover:bg-gray-100 flex items-center gap-1 text-sm">
        <i class="fas fa-sign-out-alt"></i> Logout
      </a>
    </div>
  </header>

  <!-- Main Content -->
  <main class="max-w-[100%] mx-auto p-6">

    <div class="flex justify-between items-center mb-6">
      <h2 class="text-2xl font-bold">Dashboard</h2>
      <button class="bg-green-700 hover:bg-green-800 text-white px-4 py-2 rounded-md text-sm flex items-center gap-2">
        <i class="fas fa-file-alt"></i> Cancel Report
      </button>
    </div>

    <!-- Report Form -->
    <section class="bg-white p-6 rounded-lg shadow-md max-w-70xl mx-auto mb-10">
      <h3 class="text-xl font-bold mb-4">Submit Environmental Report</h3>

      <form class="form-container space-y-4" enctype="multipart/form-data" method="POST"
        action="/GreenBin/backend/reportSubmit.php" novalidate>
        <div>
          <label for="reportTitle" class="block text-sm font-medium mb-1">Report Title</label>
          <input id="reportTitle" name="reportTitle" type="text" placeholder="e.g., Illegal dumping on Oak Street"
            required
            class="w-full border border-gray-300 p-2 rounded-md focus:ring focus:ring-green-200 focus:outline-none" />
        </div>

        <div>
          <label for="description" class="block text-sm font-medium mb-1">Description</label>
          <textarea id="description" name="description" placeholder="Description" required
            class="w-full border border-gray-300 p-2 rounded-md focus:ring focus:ring-green-200 focus:outline-none h-32"></textarea>
        </div>

        <div>
          <label class="block text-sm font-medium mb-1">Upload Photo (Optional)</label>

          <div id="uploadArea" tabindex="0" role="button" aria-label="Upload photo"
            class="upload-area cursor-pointer border border-dashed border-green-400 rounded-md p-4 text-center hover:bg-green-50 focus:outline-none focus:ring-2 focus:ring-green-400">
            <i class="fas fa-cloud-upload-alt text-3xl text-green-600 mb-2"></i>
            <p>Click to upload or drag and drop</p>
            <p class="file-info text-xs text-gray-500">PNG, JPG, GIF up to 5MB</p>
          </div>
          <input type="file" id="fileInput" name="photo" style="display:none"
            accept="image/png, image/jpeg, image/gif" />
        </div>

        <div class="pt-4">
          <button type="submit"
            class="bg-green-700 hover:bg-green-800 text-white w-full py-2 rounded-md flex justify-center items-center gap-2">
            <i class="fas fa-upload"></i> Submit
          </button>
        </div>
      </form>
    </section>

    <!-- Your Reports Section -->
    <section class="max-w-50xl mx-auto bg-white border rounded-lg shadow p-6">
      <h2 class="text-xl font-semibold text-gray-800 mb-4">Your Reports</h2>
      <p class="text-sm text-gray-500 mb-6">View the environmental reports you've submitted.</p>

      <!-- Reports container (will be populated by JavaScript) -->
      <div class="reports-list grid gap-4" id="reportsList"></div>

      <div class="no-reports text-center text-gray-500 py-8 border border-dashed rounded-md" style="display:none;"
        id="noReports">
        <p class="text-sm mb-3">No reports submitted yet.</p>
        <button class="btn-primary px-4 py-2 text-sm bg-green-600 text-white rounded hover:bg-green-700 transition">
          Submit Your First Report
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
      <h3 class="text-xl font-bold mb-4">Edit Report</h3>
      <form id="editReportForm" class="space-y-4" enctype="multipart/form-data">
        <input type="hidden" name="reportId" id="editReportId" />

        <div>
          <label for="editTitle" class="block text-sm font-medium mb-1">Title</label>
          <input type="text" id="editTitle" name="title" required
            class="w-full border border-gray-300 p-2 rounded-md" />
        </div>

        <div>
          <label for="editDescription" class="block text-sm font-medium mb-1">Description</label>
          <textarea id="editDescription" name="description" required rows="4"
            class="w-full border border-gray-300 p-2 rounded-md"></textarea>
        </div>

        <div>
          <label for="editLocation" class="block text-sm font-medium mb-1">Location</label>
          <input type="text" id="editLocation" name="location" class="w-full border border-gray-300 p-2 rounded-md" />
        </div>

        <div>
          <label class="block text-sm font-medium mb-1">New Photo (optional)</label>
          <input type="file" name="photo" id="editPhoto" accept="image/*"
            class="w-full border border-dashed p-2 rounded-md" />
        </div>

        <div class="text-right">
          <button type="submit" class="bg-green-700 hover:bg-green-800 text-white px-4 py-2 rounded-md">Save
            Changes</button>
        </div>
        <p id="editError" class="text-sm text-red-600 mt-2 hidden"></p>
      </form>
    </div>
  </div>

</body>

</html>