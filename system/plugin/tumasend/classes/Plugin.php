<?php

namespace TumaSend;

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
        if (!isTableExist('tbl_tumasend_config')) {
            ORM::raw_execute("
                CREATE TABLE `tbl_tumasend_config` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `setting_key` varchar(100) NOT NULL,
                    `setting_value` text,
                    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `setting_key` (`setting_key`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
        }
        
        // SMS history table
        if (!isTableExist('tbl_tumasend_sms_history')) {
            ORM::raw_execute("
                CREATE TABLE `tbl_tumasend_sms_history` (
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
        }
        
        // Templates table
        if (!isTableExist('tbl_tumasend_templates')) {
            ORM::raw_execute("
                CREATE TABLE `tbl_tumasend_templates` (
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
        }
        
        // Queue table
        if (!isTableExist('tbl_tumasend_queue')) {
            ORM::raw_execute("
                CREATE TABLE `tbl_tumasend_queue` (
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
        }
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
        global $ui, $admin, $routes;
        
        $ui->assign('_title', 'TumaSend Settings');
        $ui->assign('_system_menu', 'settings');
        
        $action = isset($routes[2]) ? $routes[2] : 'view';
        
        if ($action == 'save') {
            $this->saveSettings();
        }
        
        $settings = $this->config->getAll();
        $ui->assign('settings', $settings);
        $ui->assign('environment', $this->config->getEnvironment());
        
        $ui->display('plugin/admin/tumasend-settings.tpl');
    }
    
    /**
     * Save plugin settings
     */
    private function saveSettings()
    {
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
        
        _log('TumaSend settings updated', 'Admin', $admin['id']);
        r2(getUrl('plugin/tumasend/settings'), 's', 'Settings saved successfully');
    }
    
    /**
     * Admin SMS history page
     */
    public function adminHistory()
    {
        global $ui, $admin, $routes;
        
        $ui->assign('_title', 'TumaSend SMS History');
        $ui->assign('_system_menu', 'settings');
        
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
        
        $ui->display('plugin/admin/tumasend-history.tpl');
    }
    
    /**
     * Admin templates page
     */
    public function adminTemplates()
    {
        global $ui, $admin, $routes;
        
        $ui->assign('_title', 'TumaSend Message Templates');
        $ui->assign('_system_menu', 'settings');
        
        $action = isset($routes[2]) ? $routes[2] : 'view';
        
        if ($action == 'save') {
            $this->saveTemplate();
        }
        
        $templates = ORM::for_table('tbl_tumasend_templates')
            ->order_by_asc('template_name')
            ->find_many();
        
        $ui->assign('templates', $templates);
        $ui->display('plugin/admin/tumasend-templates.tpl');
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
        global $ui, $admin;
        
        $ui->assign('_title', 'TumaSend Diagnostics');
        $ui->assign('_system_menu', 'settings');
        
        $diagnostics = $this->runDiagnostics();
        $ui->assign('diagnostics', $diagnostics);
        
        $ui->display('plugin/admin/tumasend-diagnostics.tpl');
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
            'status' => isTableExist('tbl_tumasend_config') ? 'pass' : 'fail',
            'value' => isTableExist('tbl_tumasend_config') ? 'Created' : 'Missing',
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
        
        // API Connectivity
        if (!empty($apiKey)) {
            try {
                $balance = $this->api->getBalance();
                $results['api_connectivity'] = [
                    'name' => 'API Connectivity',
                    'status' => 'pass',
                    'value' => 'Connected - Credits: ' . ($balance['credits_remaining'] ?? 'Unknown'),
                    'required' => 'Connected'
                ];
            } catch (\Exception $e) {
                $results['api_connectivity'] = [
                    'name' => 'API Connectivity',
                    'status' => 'fail',
                    'value' => 'Failed: ' . $e->getMessage(),
                    'required' => 'Connected'
                ];
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
}
