<?php
session_start();
$lang = $_SESSION['lang'] ?? 'en';
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <title>Home | GreenBin Nepal</title>
  <link rel="stylesheet" href="/GreenBin/frontend/home/home.css">
</head>

<body class="bg-gray-50 text-gray-800">

  <!-- ✅ NAVBAR -->
  <nav class="bg-white shadow-sm border-b border-gray-200 p-4 flex justify-between items-center sticky top-0 z-50">
    <div class="flex items-center gap-3">
      <img src="/GreenBin/frontend/img/main-logo.png" alt="Logo" class="w-10 h-10 rounded">
      <div>
        <h1 class="text-lg font-bold text-green-700 leading-tight">हरित नेपाल</h1>
        <span class="text-xs text-gray-500">GreenBin Nepal</span>
      </div>
    </div>
    <div class="flex items-center gap-4 text-sm">
      <a href="/GreenBin/pages/lang.php?lang=<?= $lang === 'en' ? 'np' : 'en' ?>"
        class="text-xs px-2 py-1 border rounded hover:bg-gray-100">🌐
        <?= $lang === 'en' ? 'नेपाली' : 'English' ?></a>
      <a href="/GreenBin/login"
        class="px-3 py-1 bg-gray-100 text-gray-800 rounded-md hover:bg-gray-200 transition">Login</a>
      <a href="/GreenBin/register"
        class="px-3 py-1 bg-green-600 text-white rounded-md shadow hover:bg-green-700 transition">Register</a>
    </div>
  </nav>
