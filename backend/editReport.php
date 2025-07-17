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

try {
    // Check ownership
    $stmt = $pdo->prepare("SELECT * FROM reports WHERE report_id = ? AND user_id = ?");
    $stmt->execute([$reportId, $_SESSION['user_id']]);
    $report = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$report) {
        echo json_encode(['success' => false, 'message' => 'Report not found or unauthorized']);
        exit;
    }

    // Update record
    $stmtUpdate = $pdo->prepare("UPDATE reports SET title = ?, description = ?, location = ?, updated_at = NOW() WHERE report_id = ?");
    $stmtUpdate->execute([$title, $description, $location, $reportId]);

    echo json_encode([
        'success' => true,
        'message' => 'Report updated successfully',
        'report' => [
            'report_id' => $reportId,
            'title' => htmlspecialchars($title),
            'description' => htmlspecialchars($description),
            'location' => htmlspecialchars($location),
            'status' => $report['status'],
            'date' => $report['date']
        ]
    ]);
} catch (Exception $e) {
    error_log("Edit report error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred']);
}
