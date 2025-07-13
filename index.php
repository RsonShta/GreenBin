<?php
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

switch ($uri) {
    case '/':
    case '/home':
        require __DIR__ . '/pages/home.php';
        break;
    case '/login':
        require __DIR__ . '/pages/login.php';
        break;
    case '/register':
        require __DIR__ . '/pages/register.php';
        break;
    case '/dashboard':
        require __DIR__ . '/pages/dashboard.php';
        break;
    case '/superadmin':
        require __DIR__ . '/pages/superadmin.php';
        break;
    default:
        http_response_code(404);
        echo "<h1>404 Not Found</h1>";
        break;
}
