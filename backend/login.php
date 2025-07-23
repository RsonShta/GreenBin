<?php

require_once __DIR__ . '/classes/User.php';

session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['message' => 'Method Not Allowed']);
    exit;
}

$user = new User();
$result = $user->login($_POST);

http_response_code($result['code']);
echo json_encode($result);
