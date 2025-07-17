<?php
session_start();

if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'np'])) {
    $_SESSION['lang'] = $_GET['lang'];
}

// Redirect back to referring page or default to home.php
$redirect = $_SERVER['HTTP_REFERER'] ?? '/GreenBin/pages/home.php';

// Sanitize redirect to prevent header injection
$redirect = filter_var($redirect, FILTER_SANITIZE_URL);

header("Location: $redirect");
exit;
