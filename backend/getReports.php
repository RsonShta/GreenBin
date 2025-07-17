<?php
require $_SERVER['DOCUMENT_ROOT'] . '/GreenBin/backend/includes/auth.php';
requireRole(['superAdmin', 'admin', 'user']);
require $_SERVER['DOCUMENT_ROOT'] . '/GreenBin/backend/includes/db.php';

header('Content-Type: application/json');

$user_id = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("SELECT report_id, title, description, image_path, location, status, date FROM reports WHERE user_id = :user_id ORDER BY date DESC");
    $stmt->execute([':user_id' => $user_id]);
    $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'reports' => $reports]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to fetch reports']);
}
