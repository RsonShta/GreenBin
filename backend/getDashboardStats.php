<?php
require $_SERVER['DOCUMENT_ROOT'] . '/GreenBin/backend/includes/auth.php';
require $_SERVER['DOCUMENT_ROOT'] . '/GreenBin/backend/includes/db.php';

header('Content-Type: application/json');

$userId = $_SESSION['user_id'] ?? 0;

try {
    // Total Reports
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM reports WHERE user_id = ?");
    $stmt->execute([$userId]);
    $totalReports = $stmt->fetchColumn() ?: 0;

    // Pending, In Progress, Resolved
    $stmt = $pdo->prepare("
      SELECT 
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending,
        SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) AS in_progress,
        SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) AS resolved
      FROM reports WHERE user_id = ?
    ");
    $stmt->execute([$userId]);
    $statusCounts = $stmt->fetch(PDO::FETCH_ASSOC);

    $pendingCount = $statusCounts['pending'] ?? 0;
    $inProgressCount = $statusCounts['in_progress'] ?? 0;
    $resolvedCount = $statusCounts['resolved'] ?? 0;

    // CO2 Reduction
    $stmt = $pdo->prepare("SELECT SUM(co2_reduction_kg) FROM reports WHERE user_id = ?");
    $stmt->execute([$userId]);
    $co2Reduction = $stmt->fetchColumn() ?: 0;

    // Community Points (resolved * 10)
    $communityPoints = $resolvedCount * 10;

    // Resolution Rate
    $resolutionRate = $totalReports > 0 ? round(($resolvedCount / $totalReports) * 100, 1) : 0.0;

    echo json_encode([
        "success" => true,
        "stats" => [
            "totalReports" => $totalReports,
            "pendingCount" => $pendingCount,
            "inProgressCount" => $inProgressCount,
            "resolvedCount" => $resolvedCount,
            "co2Reduction" => $co2Reduction,
            "communityPoints" => $communityPoints,
            "resolutionRate" => $resolutionRate
        ]
    ]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
