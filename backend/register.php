<?php

require_once __DIR__ . '/classes/User.php';

header('Content-Type: application/json');
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = new User();
    $result = $user->register($_POST);

    http_response_code($result['code']);
    echo json_encode(['message' => $result['message']]);
} else {
    http_response_code(405);
    echo json_encode(['message' => 'Method not allowed.']);
}
