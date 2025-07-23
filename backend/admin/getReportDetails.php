<?php
require $_SERVER['DOCUMENT_ROOT'] . '/GreenBin/backend/includes/auth.php';
requireRole(['superAdmin', 'admin']);

require $_SERVER['DOCUMENT_ROOT'] . '/GreenBin/backend/includes/db.php';

$reportId = $_GET['report_id'] ?? 0;

if (!$reportId) {
    echo json_encode(["success" => false, "error" => "Invalid report ID"]);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT report_id, user_id, title, description, image_path, location, status, co2_reduction_kg 
                           FROM reports WHERE report_id = ?");
    $stmt->execute([$reportId]);
$report = $stmt->fetch(PDO::FETCH_ASSOC);

if ($report) {
    if (!empty($report['image_path'])) {
        $report['image_path'] = "/GreenBin/uploads/" . $report['image_path'];
    } else {
        $report['image_path'] = "/GreenBin/frontend/img/no-image.png";
    }
    echo json_encode(["success" => true, "report" => $report]);
} else {
    echo json_encode(["success" => false, "error" => "Report not found"]);
}
} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
