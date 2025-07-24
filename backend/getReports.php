<?php

require_once __DIR__ . '/classes/Report.php';

session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized: User not logged in.']);
    exit;
}

$userId = $_SESSION['user_id'];
$report = new Report();
$result = $report->getReportsByUserId($userId);

http_response_code($result['code']);
echo json_encode($result);
