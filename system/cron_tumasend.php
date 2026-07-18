<?php

/**
 * TumaSend Queue Processor
 * 
 * This script processes the SMS queue in the background.
 * Should be run via cron every minute.
 * 
 * Cron entry: * * * * * php /path/to/system/cron_tumasend.php
 */

// Prevent direct web access
if (php_sapi_name() !== 'cli') {
    header('HTTP/1.0 403 Forbidden');
    die('This script can only be run from CLI');
}

// Load PHPNuxBill
require_once __DIR__ . '/../init.php';

// Load plugin
require_once __DIR__ . '/plugin/tumasend.php';

use TumaSend\Plugin;

try {
    $plugin = new Plugin();
    $queue = $plugin->getQueue();
    
    // Process queue
    $results = $queue->process(50);
    
    // Log results
    echo "TumaSend Queue Processed:\n";
    echo "  Processed: " . $results['processed'] . "\n";
    echo "  Failed: " . $results['failed'] . "\n";
    echo "  Skipped (retry): " . $results['skipped'] . "\n";
    
    // Cleanup old items (older than 30 days)
    $cleaned = $queue->cleanup(30);
    if ($cleaned > 0) {
        echo "  Cleaned: " . $cleaned . " old items\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
