<?php

require_once __DIR__ . '/classes/Report.php';

session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['message' => 'Method Not Allowed']);
    exit;
}

if (!isset($_POST['reportId'])) {
    http_response_code(400);
    echo json_encode(['message' => 'Report ID is required.']);
    exit;
}

$reportId = (int)$_POST['reportId'];
$report = new Report();
$result = $report->delete($reportId);

http_response_code($result['code']);
echo json_encode($result);
