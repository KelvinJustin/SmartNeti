<?php

/**
 * SmartNeti Mobile API
 * Customer-facing REST API for Flutter mobile application
 */

// Enable CORS for mobile app
if ($_SERVER['REQUEST_METHOD'] === "OPTIONS" || $_SERVER['REQUEST_METHOD'] === "HEAD") {
    header('Access-Control-Allow-Origin: *');
    header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization, Cache-Control");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS, HEAD");
    header("Access-Control-Max-Age: 86400");
    header("HTTP/1.1 200 OK");
    die();
}

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS, HEAD');
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization, Cache-Control');

// Enable error logging
ini_set('error_log', '/var/log/php_errors.log');
ini_set('display_errors', 1);
error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);

// Error handling for JSON API
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    // Ignore Radius.php errors - they are warnings, not fatal
    if (strpos($errfile, 'Radius.php') !== false) {
        error_log("Radius warning (ignored): $errstr in $errfile on line $errline");
        return true;
    }
    // Ignore Message.php deprecation warnings
    if (strpos($errfile, 'Message.php') !== false && strpos($errstr, 'deprecated') !== false) {
        error_log("Message deprecation warning (ignored): $errstr in $errfile on line $errline");
        return true;
    }
    error_log("Error: $errstr in $errfile on line $errline");
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
    exit;
});

set_exception_handler(function($exception) {
    error_log("Exception: " . $exception->getMessage() . " in " . $exception->getFile() . " on line " . $exception->getLine());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $exception->getMessage()]);
    exit;
});

// Get request method and path
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = str_replace('/api', '', $path);
$path = trim($path, '/');

// Route the request
$segments = explode('/', $path);
$endpoint = $segments[0] ?? '';

// Include the API bootstrap
require_once __DIR__ . '/bootstrap.php';

// Route to appropriate endpoint handler
switch ($endpoint) {
    case 'login':
        require_once __DIR__ . '/endpoints/login.php';
        break;
    case 'register':
        require_once __DIR__ . '/endpoints/register.php';
        break;
    case 'profile':
        require_once __DIR__ . '/endpoints/profile.php';
        break;
    case 'packages':
        require_once __DIR__ . '/endpoints/packages.php';
        break;
    case 'balance':
        // Check for nested routes
        if (isset($segments[1]) && $segments[1] === 'transfer') {
            require_once __DIR__ . '/endpoints/balance-transfer.php';
        } else {
            require_once __DIR__ . '/endpoints/balance.php';
        }
        break;
    case 'payments':
        // Check for nested routes
        if (isset($segments[1]) && $segments[1] === 'paychangu') {
            require_once __DIR__ . '/endpoints/payments-paychangu.php';
        } else if (isset($segments[1]) && isset($segments[2]) && $segments[2] === 'status') {
            require_once __DIR__ . '/endpoints/payments-paychangu.php';
        } else {
            require_once __DIR__ . '/endpoints/payments.php';
        }
        break;
    case 'support':
        require_once __DIR__ . '/endpoints/support.php';
        break;
    case 'announcement':
        require_once __DIR__ . '/endpoints/announcement.php';
        break;
    case 'banners':
        require_once __DIR__ . '/endpoints/banners.php';
        break;
    case 'voucher':
        require_once __DIR__ . '/endpoints/voucher.php';
        break;
    case 'subscriptions':
        require_once __DIR__ . '/endpoints/subscriptions.php';
        break;
    default:
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Endpoint not found']);
        break;
}
