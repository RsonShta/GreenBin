<?php

require_once __DIR__ . '/classes/User.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['message' => 'Method Not Allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$token = $data['token'] ?? '';

$user = new User();
$result = $user->verifyEmail($token);

http_response_code($result['code']);
echo json_encode($result);
