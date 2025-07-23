<?php
session_start();

// Set or get language from session
if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'np'])) {
    $_SESSION['lang'] = $_GET['lang'];
}
$lang = $_SESSION['lang'] ?? 'en';
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $lang === 'np' ? 'पेज भेटिएन - हरित नेपाल' : 'Page Not Found - हरित नेपाल' ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        html, body {
            scrollbar-width: none; /* Firefox */
        }
        html::-webkit-scrollbar, body::-webkit-scrollbar {
            width: 0; height: 0;
        }
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="text-center">
        <img src="/GreenBin/frontend/img/mountain.png" alt="Logo" class="w-24 h-24 mx-auto mb-4">
        <h1 class="text-6xl font-bold text-red-600 mb-2">404</h1>
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">
            <?= $lang === 'np' ? 'पेज भेटिएन' : 'Page Not Found' ?>
        </h2>
        <p class="text-gray-600 mb-6">
            <?= $lang === 'np' ? 'माफ गर्नुहोस्, तपाईंले खोज्नुभएको पेज अवस्थित छैन।' : 'Sorry, the page you are looking for does not exist.' ?>
        </p>
        <a href="/GreenBin/home" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-md transition">
