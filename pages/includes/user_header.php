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

// Fetch profile photo
$stmt = $pdo->prepare("SELECT profile_photo FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$photoFileName = $stmt->fetchColumn();
$profilePhoto = $photoFileName ? '/GreenBin/uploads/profile_photo/' . htmlspecialchars($photoFileName) : '/GreenBin/frontend/img/default-profile.png';

?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= $lang === 'np' ? 'ड्यासबोर्ड - हरित नेपाल' : 'Dashboard - हरित नेपाल' ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
      <a href="/GreenBin/profile" class="flex items-center gap-2 text-gray-700 hover:text-green-700 transition">
        <img src="<?= $profilePhoto ?>" class="w-8 h-8 rounded-full border border-gray-300" alt="Profile" />
        <span><?= $lang === 'np' ? 'प्रोफाइल' : 'Profile' ?></span>
      </a>


      <!-- 🚪 Logout -->
      <a href="/GreenBin/backend/logout.php"
        class="border px-3 py-1 rounded-md text-gray-800 hover:bg-gray-100 flex items-center gap-1 text-sm transition">
        <i class="fas fa-sign-out-alt"></i> <?= $lang === 'np' ? 'लग आउट' : 'Logout' ?>
      </a>
    </div>
  </header>
