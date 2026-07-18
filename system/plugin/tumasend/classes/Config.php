<?php

namespace TumaSend;

/**
 * Configuration Manager
 * 
 * Handles plugin configuration storage and retrieval from database
 */
class Config
{
    private $cache = [];
    
    /**
     * Get configuration value
     */
    public function get($key, $default = null)
    {
        if (isset($this->cache[$key])) {
            return $this->cache[$key];
        }
        
        $config = ORM::for_table('tbl_tumasend_config')
            ->where('setting_key', $key)
            ->find_one();
        
        if ($config) {
            $value = $config->setting_value;
            $this->cache[$key] = $value;
            return $value;
        }
        
        return $default;
    }
    
    /**
     * Set configuration value
     */
    public function set($key, $value)
    {
        $config = ORM::for_table('tbl_tumasend_config')
            ->where('setting_key', $key)
            ->find_one();
        
        if ($config) {
            $config->setting_value = $value;
            $config->save();
        } else {
            $config = ORM::for_table('tbl_tumasend_config')->create();
            $config->setting_key = $key;
            $config->setting_value = $value;
            $config->save();
        }
        
        $this->cache[$key] = $value;
    }
    
    /**
     * Get all configuration values
     */
    public function getAll()
    {
        $configs = ORM::for_table('tbl_tumasend_config')->find_many();
        $result = [];
        
        foreach ($configs as $config) {
            $result[$config->setting_key] = $config->setting_value;
        }
        
        return $result;
    }
    
    /**
     * Detect environment from API key
     */
    public function getEnvironment()
    {
        $apiKey = $this->get('tumasend_api_key', '');
        
        if (strpos($apiKey, 'ts_live_') === 0) {
            return 'live';
        } elseif (strpos($apiKey, 'ts_test_') === 0) {
            return 'test';
        }
        
        return 'unknown';
    }
    
    /**
     * Get API base URL
     */
    public function getApiBaseUrl()
    {
        return $this->get('tumasend_api_base_url', 'https://gateway.tumasend.com');
    }
    
    /**
     * Get API key (masked for display)
     */
    public function getApiKey($masked = false)
    {
        $key = $this->get('tumasend_api_key', '');
        
        if ($masked && !empty($key)) {
            return substr($key, 0, 8) . '...' . substr($key, -4);
        }
        
        return $key;
    }
    
    /**
     * Get sender ID
     */
    public function getSenderId()
    {
        return $this->get('tumasend_sender_id', '');
    }
    
    /**
     * Get connection timeout
     */
    public function getConnectionTimeout()
    {
        return (int) $this->get('tumasend_connection_timeout', '30');
    }
    
    /**
     * Get retry attempts
     */
    public function getRetryAttempts()
    {
        return (int) $this->get('tumasend_retry_attempts', '3');
    }
    
    /**
     * Get retry delay
     */
    public function getRetryDelay()
    {
        return (int) $this->get('tumasend_retry_delay', '5');
    }
    
    /**
     * Check if debug logging is enabled
     */
    public function isDebugLoggingEnabled()
    {
        return $this->get('tumasend_debug_logging', '0') === '1';
    }
    
    /**
     * Check if queue is enabled
     */
    public function isQueueEnabled()
    {
        return $this->get('tumasend_queue_enabled', '0') === '1';
    }
    
    /**
     * Check if plugin is enabled
     */
    public function isEnabled()
    {
        return $this->get('tumasend_enabled', '0') === '1';
    }
}
