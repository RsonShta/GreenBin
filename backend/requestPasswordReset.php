<?php

require_once __DIR__ . '/classes/User.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['message' => 'Method Not Allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$email = $data['email'] ?? '';

$user = new User();
$result = $user->requestPasswordReset($email);

http_response_code($result['code']);
echo json_encode($result);
