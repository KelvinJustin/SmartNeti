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

// Initialize plugin
$plugin = new Plugin();
$plugin->init();

// Register hooks
register_hook('send_sms', 'tumasend_send_sms_hook');

function tumasend_send_sms_hook($args) {
    list($phone, $txt) = $args;
    $plugin = new Plugin();
    return $plugin->sendSMS($phone, $txt);
}

// Register admin menu
register_menu(
    'TumaSend',
    true,
    'tumasend_settings',
    'AFTER_PAYMENTGATEWAY',
    'ion-chatbubbles',
    '',
    'success',
    ['SuperAdmin', 'Admin']
);

// Admin functions
function tumasend_settings() {
    global $ui, $admin, $routes;
    _admin();
    $plugin = new Plugin();
    $plugin->adminSettings();
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
