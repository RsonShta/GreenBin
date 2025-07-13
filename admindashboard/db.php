<?php
$host = 'localhost'; // Use your server IP if not localhost
$dbname = 'waste_management';
$username = 'root'; // Default XAMPP username
$password = ''; // Default XAMPP password is empty

// Create connection
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
    exit;
}
?>
