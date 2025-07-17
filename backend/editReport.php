<?php
// editReport.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/GreenBin/backend/includes/auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/GreenBin/backend/includes/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$reportId = filter_input(INPUT_POST, 'reportId', FILTER_VALIDATE_INT);
$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$location = trim($_POST['location'] ?? '');

if (!$reportId || empty($title) || empty($description)) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

// Fetch existing report for authorization and existing image_path
$stmt = $pdo->prepare("SELECT * FROM reports WHERE report_id = ? AND user_id = ?");
$stmt->execute([$reportId, $_SESSION['user_id']]);
$report = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$report) {
    echo json_encode(['success' => false, 'message' => 'Report not found or unauthorized']);
    exit;
}

$uploadedImagePath = null;

// Handle file upload if a new photo is provided
if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['photo']['tmp_name'];
    $fileName = basename($_FILES['photo']['name']);
    $fileSize = $_FILES['photo']['size'];
    $fileType = mime_content_type($fileTmpPath);

    $allowedTypes = ['image/png', 'image/jpeg', 'image/jpg', 'image/gif'];

    if (!in_array($fileType, $allowedTypes)) {
        echo json_encode(['success' => false, 'message' => 'Invalid image file type']);
        exit;
    }
    if ($fileSize > 5 * 1024 * 1024) {
        echo json_encode(['success' => false, 'message' => 'File size exceeds 5MB']);
        exit;
    }

    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/GreenBin/uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $ext = pathinfo($fileName, PATHINFO_EXTENSION);
    $newFileName = uniqid('report_', true) . '.' . $ext;
    $destPath = $uploadDir . $newFileName;

    if (!move_uploaded_file($fileTmpPath, $destPath)) {
        echo json_encode(['success' => false, 'message' => 'Failed to move uploaded file']);
        exit;
    }

    $uploadedImagePath = $newFileName;

    // Optional: delete old image file if exists and not default
    if ($report['image_path'] && file_exists($uploadDir . $report['image_path'])) {
        unlink($uploadDir . $report['image_path']);
    }
}

// Update DB including image_path if new image uploaded
if ($uploadedImagePath) {
    $stmtUpdate = $pdo->prepare("UPDATE reports SET title = ?, description = ?, location = ?, image_path = ?, updated_at = NOW() WHERE report_id = ?");
    $stmtUpdate->execute([$title, $description, $location, $uploadedImagePath, $reportId]);
} else {
    $stmtUpdate = $pdo->prepare("UPDATE reports SET title = ?, description = ?, location = ?, updated_at = NOW() WHERE report_id = ?");
    $stmtUpdate->execute([$title, $description, $location, $reportId]);
}

// Fetch updated report for response
$stmtFetch = $pdo->prepare("SELECT * FROM reports WHERE report_id = ?");
$stmtFetch->execute([$reportId]);
$updatedReport = $stmtFetch->fetch(PDO::FETCH_ASSOC);

echo json_encode([
    'success' => true,
    'message' => 'Report updated successfully',
    'report' => [
        'report_id' => (int)$updatedReport['report_id'],
        'title' => htmlspecialchars($updatedReport['title']),
        'description' => htmlspecialchars($updatedReport['description']),
        'location' => htmlspecialchars($updatedReport['location']),
        'status' => $updatedReport['status'],
        'date' => $updatedReport['date'],
        'image_path' => $updatedReport['image_path']
    ]
]);
