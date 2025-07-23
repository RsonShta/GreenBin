<?php

require_once __DIR__ . '/classes/Report.php';

session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['message' => 'Method Not Allowed']);
    exit;
}

$report = new Report();
$result = $report->getAllReports();

http_response_code($result['code']);
echo json_encode($result);
