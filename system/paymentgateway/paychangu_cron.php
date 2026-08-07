<?php
/**
 * PayChangu Background Job Script
 * This script should be run periodically (e.g., every hour) via cron job
 * to check for pending transactions where webhooks may have failed
 * 
 * Add to crontab: 
 * 0 * * * * php /var/www/html/system/paymentgateway/paychangu_cron.php
 */

// Bootstrap the application
require_once dirname(__FILE__) . '/../../init.php';

// Load the PayChangu payment gateway
require_once dirname(__FILE__) . '/paychangu.php';

// Run the background job
$processed = paychangu_check_pending_transactions();

// Log the result
echo "PayChangu background job completed. Processed $processed pending transactions.\n";
