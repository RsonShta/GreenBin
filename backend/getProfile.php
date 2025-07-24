<?php

require_once __DIR__ . '/classes/User.php';

session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['message' => 'Method Not Allowed']);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['message' => 'User not authenticated.']);
    exit;
}

$user = new User();
$result = $user->getUserById($_SESSION['user_id']);

http_response_code($result['code']);
echo json_encode($result);
