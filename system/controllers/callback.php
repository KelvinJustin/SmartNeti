<?php

/**
 *  PHP Mikrotik Billing (https://github.com/hotspotbilling/phpnuxbill/)
 *  by https://t.me/ibnux
 **/

$action = $routes['1'];

// Log all callback requests for debugging
_log("Callback received: action=$action, route=" . ($_GET['_route'] ?? 'none') . ", method=" . $_SERVER['REQUEST_METHOD']);

if (file_exists($PAYMENTGATEWAY_PATH . DIRECTORY_SEPARATOR . $action . '.php')) {
    include $PAYMENTGATEWAY_PATH . DIRECTORY_SEPARATOR . $action . '.php';
    // Callback is for user-facing redirects only
    if (function_exists($action . '_callback')) {
        run_hook('callback_payment_notification'); #HOOK
        call_user_func($action . '_callback');
        die();
    }
}

header('HTTP/1.1 404 Not Found');
echo 'Not Found';
