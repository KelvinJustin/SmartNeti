<?php

/**
 *  PHP Mikrotik Billing (https://github.com/hotspotbilling/phpnuxbill/)
 *  by https://t.me/ibnux
 **/

$action = $routes['1'];

// Log all webhook requests for debugging
_log("Webhook received: action=$action, route=" . ($_GET['_route'] ?? 'none') . ", method=" . $_SERVER['REQUEST_METHOD']);

// Add test response for debugging
if (isset($_GET['test'])) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'success', 'message' => 'Webhook endpoint is reachable', 'action' => $action]);
    die();
}

if (file_exists($PAYMENTGATEWAY_PATH . DIRECTORY_SEPARATOR . $action . '.php')) {
    include $PAYMENTGATEWAY_PATH . DIRECTORY_SEPARATOR . $action . '.php';
    // Webhooks should use payment_notification function
    if (function_exists($action . '_payment_notification')) {
        run_hook('webhook_payment_notification'); #HOOK
        call_user_func($action . '_payment_notification');
        die();
    }
}

header('HTTP/1.1 404 Not Found');
echo 'Not Found';
