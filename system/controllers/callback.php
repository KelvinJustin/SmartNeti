<?php

/**
 *  PHP Mikrotik Billing (https://github.com/hotspotbilling/phpnuxbill/)
 *  by https://t.me/ibnux
 **/


$action = $routes['1'];

// Log all callback requests for debugging
_log("Callback received: action=$action, route=" . ($_GET['_route'] ?? 'none') . ", method=" . $_SERVER['REQUEST_METHOD']);

// Add test response for debugging
if (isset($_GET['test'])) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'success', 'message' => 'Webhook endpoint is reachable', 'action' => $action]);
    die();
}

if (file_exists($PAYMENTGATEWAY_PATH . DIRECTORY_SEPARATOR . $action . '.php')) {
    include $PAYMENTGATEWAY_PATH . DIRECTORY_SEPARATOR . $action . '.php';
    // Try callback function first for user-facing redirects
    if (function_exists($action . '_callback')) {
        run_hook('callback_payment_notification'); #HOOK
        call_user_func($action . '_callback');
        die();
    }
    // Fall back to payment_notification for webhooks
    elseif (function_exists($action . '_payment_notification')) {
        run_hook('callback_payment_notification'); #HOOK
        call_user_func($action . '_payment_notification');
        die();
    }
}

header('HTTP/1.1 404 Not Found');
echo 'Not Found';
