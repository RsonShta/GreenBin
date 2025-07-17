<?php
// reportSubmit.php
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');
error_reporting(E_ALL);

ob_start();
header('Content-Type: application/json');

require_once $_SERVER['DOCUMENT_ROOT'] . '/GreenBin/backend/includes/auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/GreenBin/backend/includes/db.php';

if (!isset($_SESSION['user_id'])) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'User not authenticated.']);
    exit;
}

function cleanInput($data) {
    return htmlspecialchars(trim($data));
}

$title = cleanInput($_POST['reportTitle'] ?? '');
$description = cleanInput($_POST['description'] ?? '');
$location = cleanInput($_POST['location'] ?? '');

if (empty($title) || empty($description)) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Title and Description are required.']);
    exit;
}

$uploadedImagePath = null;

if (!empty($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['photo']['tmp_name'];
    $fileName = basename($_FILES['photo']['name']);
    $fileSize = $_FILES['photo']['size'];
    $fileType = mime_content_type($fileTmpPath);

    $allowedTypes = ['image/png', 'image/jpeg', 'image/gif'];
    if (!in_array($fileType, $allowedTypes)) {
        ob_end_clean();
        echo json_encode(['success' => false, 'message' => 'Invalid file type. Only PNG, JPG, GIF allowed.']);
        exit;
    }

    if ($fileSize > 5 * 1024 * 1024) {
        ob_end_clean();
        echo json_encode(['success' => false, 'message' => 'File size exceeds 5MB limit.']);
        exit;
    }

    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/GreenBin/uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $fileExt = pathinfo($fileName, PATHINFO_EXTENSION);
    $newFileName = uniqid('report_', true) . '.' . $fileExt;
    $destPath = $uploadDir . $newFileName;

    if (!move_uploaded_file($fileTmpPath, $destPath)) {
        ob_end_clean();
        echo json_encode(['success' => false, 'message' => 'Error saving uploaded file.']);
        exit;
    }

    $uploadedImagePath = $newFileName;
}

try {
    $pdo->beginTransaction();
    $stmt = $pdo->prepare("
        INSERT INTO reports (user_id, title, description, image_path, location, status, date, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, 'pending', CURDATE(), NOW(), NOW())
    ");
    $stmt->execute([$_SESSION['user_id'], $title, $description, $uploadedImagePath, $location]);
    $newReportId = $pdo->lastInsertId();
    $pdo->commit();

    $stmtFetch = $pdo->prepare("SELECT * FROM reports WHERE report_id = ?");
    $stmtFetch->execute([$newReportId]);
    $newReport = $stmtFetch->fetch(PDO::FETCH_ASSOC);

    ob_end_clean();
    echo json_encode([
        'success' => true,
        'message' => 'Report submitted successfully',
        'report' => [
            'report_id' => (int)$newReport['report_id'],
            'title' => htmlspecialchars($newReport['title']),
            'description' => htmlspecialchars($newReport['description']),
            'location' => htmlspecialchars($newReport['location']),
            'status' => $newReport['status'],
            'date' => $newReport['date'],
            'image_path' => $newReport['image_path']
        ]
    ]);
} catch (PDOException $e) {
    $pdo->rollBack();
    error_log("DB Error in reportSubmit.php: " . $e->getMessage());
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Database error occurred.']);
}
