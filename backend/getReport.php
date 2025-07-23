<?php

require_once __DIR__ . '/classes/Report.php';

session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['message' => 'Method Not Allowed']);
    exit;
}

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['message' => 'Report ID is required.']);
    exit;
}

$reportId = (int)$_GET['id'];
$report = new Report();
$result = $report->getReportById($reportId);

http_response_code($result['code']);
echo json_encode($result);
