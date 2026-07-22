<?php

namespace TumaSend;

use ORM;
use File;
use function isTableExist;

/**
 * Main TumaSend Plugin Class
 * 
 * Handles plugin initialization, hook registration, and coordination
 * between all plugin components.
 */
class Plugin
{
    private $config;
    private $api;
    private $sms;
    private $logger;
    private $queue;
    
    public function __construct()
    {
        $this->config = new Config();
        $this->api = new API($this->config);
        $this->sms = new SMS($this->config, $this->api);
        $this->logger = new Logger();
        $this->queue = new Queue($this->config, $this->api, $this->sms, $this->logger);
    }
    
    /**
     * Initialize plugin - create tables if needed
     */
    public function init()
    {
        $this->createTables();
        $this->loadDefaultTemplates();
    }
    
    /**
     * Create database tables
     */
    private function createTables()
    {
        // Config table
        ORM::raw_execute("
            CREATE TABLE IF NOT EXISTS `tbl_tumasend_config` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `setting_key` varchar(100) NOT NULL,
                    `setting_value` text,
                    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `setting_key` (`setting_key`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
        
        // SMS history table
        ORM::raw_execute("
            CREATE TABLE IF NOT EXISTS `tbl_tumasend_sms_history` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `batch_id` varchar(100) DEFAULT NULL,
                    `recipient` varchar(20) NOT NULL,
                    `message` text NOT NULL,
                    `customer_id` int(11) DEFAULT NULL,
                    `status` enum('queued','sent','delivered','failed') DEFAULT 'queued',
                    `queued_at` timestamp DEFAULT CURRENT_TIMESTAMP,
                    `sent_at` timestamp NULL DEFAULT NULL,
                    `delivered_at` timestamp NULL DEFAULT NULL,
                    `failed_at` timestamp NULL DEFAULT NULL,
                    `credits_used` int(11) DEFAULT NULL,
                    `credits_remaining` int(11) DEFAULT NULL,
                    `environment` varchar(20) DEFAULT NULL,
                    `raw_response` text,
                    `error_message` text,
                    `retry_count` int(11) DEFAULT 0,
                    `retry_at` timestamp NULL DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    KEY `batch_id` (`batch_id`),
                    KEY `recipient` (`recipient`),
                    KEY `customer_id` (`customer_id`),
                    KEY `status` (`status`),
                    KEY `queued_at` (`queued_at`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
        
        // Templates table
        ORM::raw_execute("
            CREATE TABLE IF NOT EXISTS `tbl_tumasend_templates` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `template_key` varchar(100) NOT NULL,
                    `template_name` varchar(255) NOT NULL,
                    `template_content` text NOT NULL,
                    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `template_key` (`template_key`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
        
        // Queue table
        ORM::raw_execute("
            CREATE TABLE IF NOT EXISTS `tbl_tumasend_queue` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `recipient` varchar(20) NOT NULL,
                    `message` text NOT NULL,
                    `customer_id` int(11) DEFAULT NULL,
                    `priority` enum('high','normal','low') DEFAULT 'normal',
                    `scheduled_at` timestamp NULL DEFAULT NULL,
                    `attempts` int(11) DEFAULT 0,
                    `max_attempts` int(11) DEFAULT 3,
                    `next_attempt_at` timestamp NULL DEFAULT NULL,
                    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
                    `status` enum('pending','processing','completed','failed') DEFAULT 'pending',
                    `error_message` text,
                    PRIMARY KEY (`id`),
                    KEY `status` (`status`),
                    KEY `next_attempt_at` (`next_attempt_at`),
                    KEY `priority` (`priority`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");

        // Data usage alerts tracking table
        ORM::raw_execute("
            CREATE TABLE IF NOT EXISTS `tbl_tumasend_data_alerts` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `customer_id` int(11) NOT NULL,
                    `recharge_id` int(11) NOT NULL,
                    `threshold` int(11) NOT NULL,
                    `alerted_at` timestamp DEFAULT CURRENT_TIMESTAMP,
                    `usage_percent` decimal(5,2) DEFAULT NULL,
                    `usage_mb` decimal(10,2) DEFAULT NULL,
                    `limit_mb` decimal(10,2) DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `customer_recharge_threshold` (`customer_id`, `recharge_id`, `threshold`),
                    KEY `customer_id` (`customer_id`),
                    KEY `recharge_id` (`recharge_id`),
                    KEY `alerted_at` (`alerted_at`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
    }
    
    /**
     * Load default message templates
     */
    private function loadDefaultTemplates()
    {
        $defaults = [
            'payment_confirmation' => 'Payment Confirmation',
            'voucher_delivery' => 'Voucher Delivery',
            'password_reset' => 'Password Reset',
            'account_activation' => 'Account Activation',
            'package_activation' => 'Package Activation',
            'package_expiration' => 'Package Expiration',
            'low_balance' => 'Low Balance Alert',
            'data_usage_50' => 'Data Usage 50% Alert',
            'data_usage_80' => 'Data Usage 80% Alert',
            'data_usage_100' => 'Data Usage 100% Alert',
        ];
        
        foreach ($defaults as $key => $name) {
            $existing = ORM::for_table('tbl_tumasend_templates')
                ->where('template_key', $key)
                ->find_one();
            
            if (!$existing) {
                $template = ORM::for_table('tbl_tumasend_templates')->create();
                $template->template_key = $key;
                $template->template_name = $name;
                $template->template_content = "Dear {customer_name},\n\nYour message content here.";
                $template->save();
            }
        }
    }
    
    /**
     * Send SMS - hook implementation
     */
    public function sendSMS($phone, $message)
    {
        // Check if plugin is enabled
        if (!$this->config->get('tumasend_enabled', '0')) {
            return false; // Let other hooks handle it
        }
        
        try {
            $result = $this->sms->send($phone, $message);
            return $result ? true : false;
        } catch (\Exception $e) {
            $this->logger->log('SMS Error', $phone, $message, 'Error', $e->getMessage());
            return false;
        }
    }
    
    /**
     * Admin settings page
     */
    public function adminSettings()
    {
        global $ui, $admin, $routes, $menu_registered;
        
        $ui->assign('_title', 'TumaSend Settings');
        $ui->assign('_system_menu', 'tumasend');
        $ui->assign('_admin', $admin);
        
        // Build menu variables manually
        $menus = [];
        foreach ($menu_registered as $menu) {
            if ($menu['admin'] && function_exists('_admin') && _admin(false)) {
                if (count($menu['auth']) == 0 || in_array($admin['user_type'], $menu['auth'])) {
                    $menus[$menu['position']] .= '<li' . (($routes[1] == $menu['function']) ? ' class="active"' : '') . '><a href="' . getUrl('plugin/' . $menu['function']) . '">';
                    if (!empty($menu['icon'])) {
                        $menus[$menu['position']] .= '<i class="' . $menu['icon'] . '"></i>';
                    }
                    if (!empty($menu['label'])) {
                        $menus[$menu['position']] .= '<span class="pull-right-container">';
                        $menus[$menu['position']] .= '<small class="label pull-right bg-' . $menu['color'] . '">' . $menu['label'] . '</small></span>';
                    }
                    $menus[$menu['position']] .= '<span class="text">' . $menu['name'] . '</span></a></li>';
                }
            }
        }
        
        foreach ($menus as $k => $v) {
            $ui->assign('_MENU_' . $k, $v);
        }
        
        $action = isset($routes[3]) ? $routes[3] : 'view';
        
        if ($action == 'save') {
            $this->saveSettings();
        }
        
        $settings = $this->config->getAll();
        $ui->assign('settings', $settings);
        $ui->assign('environment', $this->config->getEnvironment());
        
        global $root_path;
        $ui->display($root_path . 'system/plugin/ui/admin/tumasend-settings.tpl');
    }
    
    /**
     * Save plugin settings
     */
    private function saveSettings()
    {
        global $admin;
        
        $settings = [
            'tumasend_enabled' => _post('tumasend_enabled', '0'),
            'tumasend_api_key' => _post('tumasend_api_key', ''),
            'tumasend_sender_id' => _post('tumasend_sender_id', ''),
            'tumasend_api_base_url' => _post('tumasend_api_base_url', 'https://gateway.tumasend.com'),
            'tumasend_connection_timeout' => _post('tumasend_connection_timeout', '30'),
            'tumasend_retry_attempts' => _post('tumasend_retry_attempts', '3'),
            'tumasend_retry_delay' => _post('tumasend_retry_delay', '5'),
            'tumasend_debug_logging' => _post('tumasend_debug_logging', '0'),
            'tumasend_webhook_secret' => _post('tumasend_webhook_secret', ''),
            'tumasend_webhook_enabled' => _post('tumasend_webhook_enabled', '0'),
            'tumasend_queue_enabled' => _post('tumasend_queue_enabled', '0'),
        ];
        
        foreach ($settings as $key => $value) {
            $this->config->set($key, $value);
        }
        
        _log('TumaSend settings updated', 'Admin', isset($admin['id']) ? $admin['id'] : 0);
        r2(getUrl('plugin/tumasend/settings'), 's', 'Settings saved successfully');
    }
    
    /**
     * Admin SMS history page
     */
    public function adminHistory()
    {
        global $ui, $admin, $routes, $menu_registered;
        
        $ui->assign('_title', 'TumaSend SMS History');
        $ui->assign('_system_menu', 'tumasend');
        $ui->assign('_admin', $admin);
        
        // Build menu variables manually
        $menus = [];
        foreach ($menu_registered as $menu) {
            if ($menu['admin'] && function_exists('_admin') && _admin(false)) {
                if (count($menu['auth']) == 0 || in_array($admin['user_type'], $menu['auth'])) {
                    $menus[$menu['position']] .= '<li' . (($routes[1] == $menu['function']) ? ' class="active"' : '') . '><a href="' . getUrl('plugin/' . $menu['function']) . '">';
                    if (!empty($menu['icon'])) {
                        $menus[$menu['position']] .= '<i class="' . $menu['icon'] . '"></i>';
                    }
                    if (!empty($menu['label'])) {
                        $menus[$menu['position']] .= '<span class="pull-right-container">';
                        $menus[$menu['position']] .= '<small class="label pull-right bg-' . $menu['color'] . '">' . $menu['label'] . '</small></span>';
                    }
                    $menus[$menu['position']] .= '<span class="text">' . $menu['name'] . '</span></a></li>';
                }
            }
        }
        
        foreach ($menus as $k => $v) {
            $ui->assign('_MENU_' . $k, $v);
        }
        
        $page = isset($routes[2]) && is_numeric($routes[2]) ? $routes[2] : 1;
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        $query = ORM::for_table('tbl_tumasend_sms_history')
            ->order_by_desc('queued_at')
            ->limit($limit)
            ->offset($offset);
        
        // Apply filters
        if ($status = _get('status')) {
            $query->where('status', $status);
        }
        if ($search = _get('search')) {
            $query->where_like('recipient', '%' . $search . '%')
                  ->or_where_like('message', '%' . $search . '%');
        }
        
        $messages = $query->find_many();
        $total = ORM::for_table('tbl_tumasend_sms_history')->count();
        
        $ui->assign('messages', $messages);
        $ui->assign('page', $page);
        $ui->assign('total', $total);
        $ui->assign('total_pages', ceil($total / $limit));
        
        global $root_path;
        $ui->display($root_path . 'system/plugin/ui/admin/tumasend-history.tpl');
    }
    
    /**
     * Admin templates page
     */
    public function adminTemplates()
    {
        global $ui, $admin, $routes, $menu_registered;
        
        $ui->assign('_title', 'TumaSend Message Templates');
        $ui->assign('_system_menu', 'tumasend');
        $ui->assign('_admin', $admin);
        
        // Build menu variables manually
        $menus = [];
        foreach ($menu_registered as $menu) {
            if ($menu['admin'] && function_exists('_admin') && _admin(false)) {
                if (count($menu['auth']) == 0 || in_array($admin['user_type'], $menu['auth'])) {
                    $menus[$menu['position']] .= '<li' . (($routes[1] == $menu['function']) ? ' class="active"' : '') . '><a href="' . getUrl('plugin/' . $menu['function']) . '">';
                    if (!empty($menu['icon'])) {
                        $menus[$menu['position']] .= '<i class="' . $menu['icon'] . '"></i>';
                    }
                    if (!empty($menu['label'])) {
                        $menus[$menu['position']] .= '<span class="pull-right-container">';
                        $menus[$menu['position']] .= '<small class="label pull-right bg-' . $menu['color'] . '">' . $menu['label'] . '</small></span>';
                    }
                    $menus[$menu['position']] .= '<span class="text">' . $menu['name'] . '</span></a></li>';
                }
            }
        }
        
        foreach ($menus as $k => $v) {
            $ui->assign('_MENU_' . $k, $v);
        }
        
        $action = isset($routes[2]) ? $routes[2] : 'view';
        
        if ($action == 'save') {
            $this->saveTemplate();
        }
        
        $templates = ORM::for_table('tbl_tumasend_templates')
            ->order_by_asc('template_name')
            ->find_many();
        
        $ui->assign('templates', $templates);
        
        global $root_path;
        $ui->display($root_path . 'system/plugin/ui/admin/tumasend-templates.tpl');
    }
    
    /**
     * Save message template
     */
    private function saveTemplate()
    {
        $template_key = _post('template_key');
        $template_content = _post('template_content');
        
        $template = ORM::for_table('tbl_tumasend_templates')
            ->where('template_key', $template_key)
            ->find_one();
        
        if ($template) {
            $template->template_content = $template_content;
            $template->save();
        }
        
        _log('TumaSend template updated: ' . $template_key, 'Admin', $admin['id']);
        r2(getUrl('plugin/tumasend/templates'), 's', 'Template saved successfully');
    }
    
    /**
     * Admin diagnostics page
     */
    public function adminDiagnostics()
    {
        global $ui, $admin, $routes, $menu_registered;
        
        $ui->assign('_title', 'TumaSend Diagnostics');
        $ui->assign('_system_menu', 'tumasend');
        $ui->assign('_admin', $admin);
        
        // Build menu variables manually
        $menus = [];
        foreach ($menu_registered as $menu) {
            if ($menu['admin'] && function_exists('_admin') && _admin(false)) {
                if (count($menu['auth']) == 0 || in_array($admin['user_type'], $menu['auth'])) {
                    $menus[$menu['position']] .= '<li' . (($routes[1] == $menu['function']) ? ' class="active"' : '') . '><a href="' . getUrl('plugin/' . $menu['function']) . '">';
                    if (!empty($menu['icon'])) {
                        $menus[$menu['position']] .= '<i class="' . $menu['icon'] . '"></i>';
                    }
                    if (!empty($menu['label'])) {
                        $menus[$menu['position']] .= '<span class="pull-right-container">';
                        $menus[$menu['position']] .= '<small class="label pull-right bg-' . $menu['color'] . '">' . $menu['label'] . '</small></span>';
                    }
                    $menus[$menu['position']] .= '<span class="text">' . $menu['name'] . '</span></a></li>';
                }
            }
        }
        
        foreach ($menus as $k => $v) {
            $ui->assign('_MENU_' . $k, $v);
        }
        
        $diagnostics = $this->runDiagnostics();
        $ui->assign('diagnostics', $diagnostics);
        
        global $root_path;
        $ui->display($root_path . 'system/plugin/ui/admin/tumasend-diagnostics.tpl');
    }
    
    /**
     * Run diagnostic checks
     */
    private function runDiagnostics()
    {
        $results = [];
        
        // PHP Version
        $results['php_version'] = [
            'name' => 'PHP Version',
            'status' => version_compare(PHP_VERSION, '7.4', '>=') ? 'pass' : 'fail',
            'value' => PHP_VERSION,
            'required' => '>= 7.4'
        ];
        
        // cURL Extension
        $results['curl'] = [
            'name' => 'cURL Extension',
            'status' => extension_loaded('curl') ? 'pass' : 'fail',
            'value' => extension_loaded('curl') ? 'Enabled' : 'Disabled',
            'required' => 'Enabled'
        ];
        
        // JSON Extension
        $results['json'] = [
            'name' => 'JSON Extension',
            'status' => extension_loaded('json') ? 'pass' : 'fail',
            'value' => extension_loaded('json') ? 'Enabled' : 'Disabled',
            'required' => 'Enabled'
        ];
        
        // OpenSSL Extension
        $results['openssl'] = [
            'name' => 'OpenSSL Extension',
            'status' => extension_loaded('openssl') ? 'pass' : 'fail',
            'value' => extension_loaded('openssl') ? 'Enabled' : 'Disabled',
            'required' => 'Enabled'
        ];
        
        // Database Tables
        $results['tables'] = [
            'name' => 'Database Tables',
            'status' => \isTableExist('tbl_tumasend_config') ? 'pass' : 'fail',
            'value' => \isTableExist('tbl_tumasend_config') ? 'Created' : 'Missing',
            'required' => 'Created'
        ];
        
        // API Configuration
        $apiKey = $this->config->get('tumasend_api_key');
        $results['api_config'] = [
            'name' => 'API Configuration',
            'status' => !empty($apiKey) ? 'pass' : 'warn',
            'value' => !empty($apiKey) ? 'Configured' : 'Not configured',
            'required' => 'Configured'
        ];
        
        // API Connectivity - Test SMS sending instead of balance check
        if (!empty($apiKey)) {
            try {
                // Try a minimal SMS send test (will fail due to credits but validates API)
                $senderId = $this->config->getSenderId();
                $testResult = $this->api->sendSMS(
                    $senderId ?: 'TumaSend',
                    ['+265893233816'], // Valid Malawi test number
                    'API connectivity test'
                );
                $results['api_connectivity'] = [
                    'name' => 'API Connectivity',
                    'status' => 'pass',
                    'value' => 'Connected - SMS API working',
                    'required' => 'Connected'
                ];
            } catch (\Exception $e) {
                // If error is about credits, API is working
                $errorMsg = $e->getMessage();
                if (strpos($errorMsg, 'credits') !== false || strpos($errorMsg, '402') !== false) {
                    $results['api_connectivity'] = [
                        'name' => 'API Connectivity',
                        'status' => 'pass',
                        'value' => 'Connected - SMS API working (credits needed)',
                        'required' => 'Connected'
                    ];
                } else {
                    $results['api_connectivity'] = [
                        'name' => 'API Connectivity',
                        'status' => 'fail',
                        'value' => 'Failed: ' . $errorMsg,
                        'required' => 'Connected'
                    ];
                }
            }
        } else {
            $results['api_connectivity'] = [
                'name' => 'API Connectivity',
                'status' => 'skip',
                'value' => 'Skipped - API key not configured',
                'required' => 'Connected'
            ];
        }
        
        return $results;
    }
    
    /**
     * Handle webhook from TumaSend
     */
    public function handleWebhook()
    {
        if (!$this->config->get('tumasend_webhook_enabled', '0')) {
            http_response_code(404);
            echo 'Webhooks disabled';
            exit;
        }
        
        $rawBody = file_get_contents('php://input');
        $signature = $_SERVER['HTTP_TUMASEND_SIGNATURE'] ?? '';
        $secret = $this->config->get('tumasend_webhook_secret');
        
        if (!$this->verifyWebhookSignature($rawBody, $signature, $secret)) {
            http_response_code(401);
            echo 'Invalid signature';
            exit;
        }
        
        $payload = json_decode($rawBody, true);
        
        if (!$payload) {
            http_response_code(400);
            echo 'Invalid JSON';
            exit;
        }
        
        $this->processWebhookEvent($payload);
        
        http_response_code(200);
        echo 'OK';
        exit;
    }
    
    /**
     * Verify webhook signature
     */
    private function verifyWebhookSignature($rawBody, $signature, $secret)
    {
        if (empty($secret)) {
            return false;
        }
        
        $expected = 'sha256=' . hash_hmac('sha256', $rawBody, $secret);
        return hash_equals($expected, $signature);
    }
    
    /**
     * Process webhook event
     */
    private function processWebhookEvent($payload)
    {
        $eventType = $payload['event'] ?? '';
        $data = $payload['data'] ?? [];
        
        $this->logger->log('Webhook Event', '', json_encode($payload), 'Success');
        
        switch ($eventType) {
            case 'message.delivered':
                $this->updateMessageStatus($data['batch_id'] ?? null, $data['recipient'] ?? null, 'delivered');
                break;
            case 'message.failed':
                $this->updateMessageStatus($data['batch_id'] ?? null, $data['recipient'] ?? null, 'failed', $data['error'] ?? null);
                break;
        }
    }
    
    /**
     * Update message status from webhook
     */
    private function updateMessageStatus($batchId, $recipient, $status, $errorMessage = null)
    {
        $message = ORM::for_table('tbl_tumasend_sms_history')
            ->where('batch_id', $batchId)
            ->where('recipient', $recipient)
            ->find_one();
        
        if ($message) {
            $message->status = $status;
            
            if ($status == 'delivered') {
                $message->delivered_at = date('Y-m-d H:i:s');
            } elseif ($status == 'failed') {
                $message->failed_at = date('Y-m-d H:i:s');
                $message->error_message = $errorMessage;
            }
            
            $message->save();
        }
    }
    
    /**
     * Send test SMS
     */
    public function testSMS()
    {
        $recipient = _post('test_recipient');
        $message = _post('test_message', 'This is a test message from TumaSend plugin.');
        
        if (empty($recipient)) {
            r2(getUrl('plugin/tumasend/settings'), 'e', 'Recipient number is required');
        }
        
        try {
            $result = $this->sms->send($recipient, $message);
            
            if ($result) {
                r2(getUrl('plugin/tumasend/settings'), 's', 'Test SMS sent successfully');
            } else {
                r2(getUrl('plugin/tumasend/settings'), 'e', 'Failed to send test SMS');
            }
        } catch (\Exception $e) {
            r2(getUrl('plugin/tumasend/settings'), 'e', 'Error: ' . $e->getMessage());
        }
    }
    
    /**
     * Get API balance
     */
    public function getAPIBalance()
    {
        try {
            $balance = $this->api->getBalance();
            showResult(true, 'API Balance retrieved', $balance);
        } catch (\Exception $e) {
            showResult(false, $e->getMessage());
        }
    }
    
    /**
     * Get queue instance (for cron processing)
     */
    public function getQueue()
    {
        return $this->queue;
    }

    /**
     * Check if specific alert type is enabled
     */
    public function isAlertTypeEnabled($message)
    {
        // Determine alert type based on message content
        $alertType = $this->detectAlertType($message);

        if ($alertType === 'unknown') {
            return true; // Allow unknown message types by default
        }

        $settingKey = 'alert_' . $alertType;
        $enabled = $this->config->get($settingKey);

        return $enabled === '1';
    }

    /**
     * Detect alert type from message content
     */
    private function detectAlertType($message)
    {
        $messageLower = strtolower($message);

        // Check for data usage alerts
        if (strpos($messageLower, 'data limit') !== false ||
            strpos($messageLower, 'data usage') !== false ||
            strpos($messageLower, '% of your data') !== false) {
            return 'data_usage';
        }

        // Check for payment confirmation
        if (strpos($messageLower, 'payment') !== false &&
            strpos($messageLower, 'confirmation') !== false) {
            return 'payment_confirmation';
        }

        // Check for voucher delivery
        if (strpos($messageLower, 'voucher') !== false) {
            return 'voucher_delivery';
        }

        // Check for password reset
        if (strpos($messageLower, 'password') !== false &&
            strpos($messageLower, 'reset') !== false) {
            return 'password_reset';
        }

        // Check for account activation
        if (strpos($messageLower, 'account') !== false &&
            strpos($messageLower, 'activation') !== false) {
            return 'account_activation';
        }

        // Check for package activation
        if (strpos($messageLower, 'package') !== false &&
            strpos($messageLower, 'activation') !== false) {
            return 'package_activation';
        }

        // Check for package expiration
        if (strpos($messageLower, 'package') !== false &&
            strpos($messageLower, 'expir') !== false) {
            return 'package_expiration';
        }

        // Check for low balance
        if (strpos($messageLower, 'balance') !== false &&
            strpos($messageLower, 'low') !== false) {
            return 'low_balance';
        }

        // Check for invoice
        if (strpos($messageLower, 'invoice') !== false) {
            return 'invoice';
        }

        return 'unknown';
    }

    /**
     * Monitor data usage and send alerts at thresholds
     * Called by cron job every minute
     */
    public function monitorDataUsage()
    {
        // Check if data usage monitoring is enabled
        $enabled = $this->config->get('data_usage_alerts_enabled');
        if ($enabled !== 'yes') {
            return;
        }

        try {
            // Get configured thresholds
            $thresholdsConfig = $this->config->get('data_usage_thresholds');
            $thresholds = $thresholdsConfig ? explode(',', $thresholdsConfig) : [50, 80, 100];
            $thresholds = array_map('trim', $thresholds);
            $thresholds = array_map('intval', $thresholds);
            sort($thresholds);

            // Get active plans with data limits
            $activePlans = ORM::for_table('tbl_user_recharges')
                ->where('status', 'on')
                ->join('tbl_plans', ['tbl_user_recharges.plan_id', '=', 'tbl_plans.id'])
                ->where_in('tbl_plans.limit_type', ['Data_Limit', 'Both_Limit'])
                ->where_in('tbl_plans.typebp', ['Limited'])
                ->find_many();

            if (empty($activePlans)) {
                return;
            }

            foreach ($activePlans as $recharge) {
                $this->checkCustomerDataUsage($recharge, $thresholds);
            }
        } catch (\Exception $e) {
            $this->logger->error('Data usage monitoring error: ' . $e->getMessage());
        }
    }

    /**
     * Check individual customer data usage
     */
    private function checkCustomerDataUsage($recharge, $thresholds)
    {
        try {
            $plan = ORM::for_table('tbl_plans')->where('id', $recharge['plan_id'])->find_one();
            $customer = ORM::for_table('tbl_customers')->where('id', $recharge['customer_id'])->find_one();

            if (!$plan || !$customer) {
                return;
            }

            // Calculate data limit in MB
            $limitMb = $this->calculateDataLimitMb($plan);
            if ($limitMb <= 0) {
                return;
            }

            // Get current usage
            $usageMb = $this->getCurrentDataUsage($customer['username'], $recharge);
            if ($usageMb < 0) {
                return;
            }

            // Calculate usage percentage
            $usagePercent = ($usageMb / $limitMb) * 100;

            // Check thresholds
            foreach ($thresholds as $threshold) {
                if ($usagePercent >= $threshold) {
                    $this->sendDataUsageAlert($customer, $recharge, $plan, $usagePercent, $usageMb, $limitMb, $threshold);
                }
            }
        } catch (\Exception $e) {
            $this->logger->error('Error checking customer data usage: ' . $e->getMessage());
        }
    }

    /**
     * Calculate data limit in MB
     */
    private function calculateDataLimitMb($plan)
    {
        $limit = $plan['data_limit'];
        $unit = $plan['data_unit'];

        switch ($unit) {
            case 'GB':
                return $limit * 1024;
            case 'MB':
                return $limit;
            default:
                return 0;
        }
    }

    /**
     * Get current data usage from router or database
     */
    private function getCurrentDataUsage($username, $recharge)
    {
        try {
            // Try to get usage from radius accounting table first
            $usage = ORM::for_table('rad_acct')
                ->where('username', $username)
                ->where('acctstarttime', '>=', $recharge['recharged_on'])
                ->select_expr('SUM(acctinputoctets + acctoutputoctets)', 'total_bytes')
                ->find_one();

            if ($usage && $usage['total_bytes']) {
                return $usage['total_bytes'] / (1024 * 1024); // Convert to MB
            }

            // Fallback: try to get from Mikrotik router
            $router = ORM::for_table('tbl_routers')->where('id', $recharge['routers'])->find_one();
            if ($router) {
                return $this->getMikrotikDataUsage($router, $username);
            }

            return 0;
        } catch (\Exception $e) {
            $this->logger->error('Error getting data usage: ' . $e->getMessage());
            return -1;
        }
    }

    /**
     * Get data usage from Mikrotik router
     */
    private function getMikrotikDataUsage($router, $username)
    {
        try {
            // This would require Mikrotik API integration
            // For now, return 0 as fallback
            return 0;
        } catch (\Exception $e) {
            $this->logger->error('Error getting Mikrotik data usage: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Send data usage alert
     */
    private function sendDataUsageAlert($customer, $recharge, $plan, $usagePercent, $usageMb, $limitMb, $threshold)
    {
        try {
            // Check if alert already sent for this threshold
            $existingAlert = ORM::for_table('tbl_tumasend_data_alerts')
                ->where('customer_id', $customer['id'])
                ->where('recharge_id', $recharge['id'])
                ->where('threshold', $threshold)
                ->find_one();

            if ($existingAlert) {
                return; // Already alerted for this threshold
            }

            // Get template
            $templateKey = 'data_usage_' . $threshold;
            $template = ORM::for_table('tbl_tumasend_templates')
                ->where('template_key', $templateKey)
                ->find_one();

            if (!$template) {
                $message = "Dear {$customer['fullname']}, you have used {$usagePercent}% of your data limit ({$usageMb}MB / {$limitMb}MB).";
            } else {
                $message = $this->replaceTemplatePlaceholders($template['template_content'], $customer, $plan, $usagePercent, $usageMb, $limitMb);
            }

            // Send SMS
            if (!empty($customer['phonenumber'])) {
                $this->sms->send($customer['phonenumber'], $message);
            }

            // Record alert
            $alert = ORM::for_table('tbl_tumasend_data_alerts')->create();
            $alert->customer_id = $customer['id'];
            $alert->recharge_id = $recharge['id'];
            $alert->threshold = $threshold;
            $alert->usage_percent = $usagePercent;
            $alert->usage_mb = $usageMb;
            $alert->limit_mb = $limitMb;
            $alert->save();

        } catch (\Exception $e) {
            $this->logger->error('Error sending data usage alert: ' . $e->getMessage());
        }
    }

    /**
     * Replace template placeholders
     */
    private function replaceTemplatePlaceholders($template, $customer, $plan, $usagePercent, $usageMb, $limitMb)
    {
        $replacements = [
            '{customer_name}' => $customer['fullname'],
            '{customer_username}' => $customer['username'],
            '{plan_name}' => $plan['name'],
            '{usage_percent}' => number_format($usagePercent, 2),
            '{usage_mb}' => number_format($usageMb, 2),
            '{limit_mb}' => number_format($limitMb, 2),
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }
}






