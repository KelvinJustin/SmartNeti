<?php

/**
 * TumaSend Communications Plugin for PHPNuxBill
 * 
 * A production-ready plugin integrating TumaSend's Communications API
 * for SMS, WhatsApp, Email, and OTP services.
 * 
 * @author TumaSend Plugin
 * @version 1.0.0
 * @license MIT
 */

// Prevent direct access
if (realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME'])) {
    header('HTTP/1.0 403 Forbidden', TRUE, 403);
    die();
}

// Autoload plugin classes
spl_autoload_register(function ($class) {
    global $root_path;
    $prefix = 'TumaSend\\';
    $base_dir = $root_path . 'system/plugin/tumasend/classes/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});

use TumaSend\Plugin;

// Register hooks (tables will be created when admin first accesses settings)
register_hook('send_sms', 'tumasend_send_sms_hook');
register_hook('cronjob', 'tumasend_data_usage_monitor');

function tumasend_send_sms_hook($args) {
    list($phone, $txt) = $args;
    $plugin = new Plugin();

    // Check if specific alert type is enabled
    if (!$plugin->isAlertTypeEnabled($txt)) {
        return false;
    }

    return $plugin->sendSMS($phone, $txt);
}

function tumasend_data_usage_monitor() {
    $plugin = new Plugin();
    $plugin->monitorDataUsage();
}

// Register admin menu
register_menu(
    '    SMS Config',
    true,
    'tumasend',
    'AFTER_SETTINGS',
    'ion-chatbubbles',
    '',
    'success',
    []
);

// Admin functions
function tumasend() {
    global $routes;
    
    $action = isset($routes[2]) ? $routes[2] : 'settings';
    
    switch ($action) {
        case 'settings':
            tumasend_settings();
            break;
        case 'test':
            tumasend_test();
            break;
        case 'history':
            tumasend_history();
            break;
        case 'templates':
            tumasend_templates();
            break;
        case 'diagnostics':
            tumasend_diagnostics();
            break;
        default:
            tumasend_settings();
            break;
    }
}

function tumasend_settings() {
    global $ui, $admin, $routes;
    _admin();
    $plugin = new Plugin();
    $plugin->init(); // Initialize tables on first access
    $plugin->adminSettings();
}

function tumasend_test() {
    global $ui, $admin, $routes;
    _admin();
    $plugin = new Plugin();
    $plugin->init();
    
    $recipient = _post('test_recipient');
    $message = _post('test_message');
    
    if ($recipient && $message) {
        try {
            $result = $plugin->sendSMS($recipient, $message);
            if ($result) {
                r2(getUrl('plugin/tumasend/settings'), 's', 'Test SMS sent successfully');
            } else {
                r2(getUrl('plugin/tumasend/settings'), 'e', 'Test SMS failed - check plugin settings and API key');
            }
        } catch (\Exception $e) {
            r2(getUrl('plugin/tumasend/settings'), 'e', 'Test SMS error: ' . $e->getMessage());
        }
    } else {
        r2(getUrl('plugin/tumasend/settings'), 'e', 'Please provide recipient and message');
    }
}

function tumasend_history() {
    global $ui, $admin, $routes;
    _admin();
    $plugin = new Plugin();
    $plugin->adminHistory();
}

function tumasend_templates() {
    global $ui, $admin, $routes;
    _admin();
    $plugin = new Plugin();
    $plugin->adminTemplates();
}

function tumasend_diagnostics() {
    global $ui, $admin, $routes;
    _admin();
    $plugin = new Plugin();
    $plugin->adminDiagnostics();
}

function tumasend_webhook() {
    $plugin = new Plugin();
    $plugin->handleWebhook();
}

function tumasend_test_sms() {
    global $ui, $admin;
    _admin();
    $plugin = new Plugin();
    $plugin->testSMS();
}

function tumasend_api_balance() {
    global $ui, $admin;
    _admin();
    $plugin = new Plugin();
    $plugin->getAPIBalance();
}



