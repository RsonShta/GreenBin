<?php
// deleteReport.php
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

if (!$reportId) {
    echo json_encode(['success' => false, 'message' => 'Invalid report ID']);
    exit;
}

try {
    // Check ownership
    $stmt = $pdo->prepare("SELECT image_path FROM reports WHERE report_id = ? AND user_id = ?");
    $stmt->execute([$reportId, $_SESSION['user_id']]);
    $report = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$report) {
        echo json_encode(['success' => false, 'message' => 'Report not found or not authorized']);
        exit;
    }

    // Delete image file if exists
    if ($report['image_path']) {
        $imageFile = $_SERVER['DOCUMENT_ROOT'] . '/GreenBin/uploads/' . $report['image_path'];
        if (file_exists($imageFile)) {
            unlink($imageFile);
        }
    }

    // Delete report from DB
    $stmtDel = $pdo->prepare("DELETE FROM reports WHERE report_id = ?");
    $stmtDel->execute([$reportId]);

    echo json_encode(['success' => true, 'message' => 'Report deleted successfully']);
} catch (Exception $e) {
    error_log("Delete report error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred']);
}
