<?php
session_start();

// Redirect to login if user is not logged in or not admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: /GreenBin/frontend/login/login.html");
    exit();
}

// Optional: access session data
$userId = $_SESSION['user_id'];
$userName = htmlspecialchars($_SESSION['user_name']);
$userEmail = htmlspecialchars($_SESSION['user_email'] ?? 'admin@example.com');
?>
