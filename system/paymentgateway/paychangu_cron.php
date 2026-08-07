<?php
/**
 * PayChangu Background Job Script
 * This script should be run periodically (e.g., every 5-15 minutes) via cron job
 * to check for pending transactions where webhooks may have failed
 * 
 * Recommended cron schedules:
 * - Every 5 minutes: */5 * * * * php /var/www/html/system/paymentgateway/paychangu_cron.php
 * - Every 15 minutes: */15 * * * * php /var/www/html/system/paymentgateway/paychangu_cron.php
 * - Every 30 minutes: */30 * * * * php /var/www/html/system/paymentgateway/paychangu_cron.php
 * 
 * Running every minute is NOT recommended to avoid API rate limits
 */

// Bootstrap the application
require_once dirname(__FILE__) . '/../../init.php';

// Load the PayChangu payment gateway
require_once dirname(__FILE__) . '/paychangu.php';

// Check if there are any pending transactions before making API calls
$pendingCount = ORM::for_table('tbl_payment_gateway')
    ->where('gateway', 'PayChangu')
    ->where('status', 1) // Pending status
    ->where_gt('expired_date', date('Y-m-d H:i:s'))
    ->count();

if ($pendingCount == 0) {
    echo "No pending PayChangu transactions found. Skipping API checks.\n";
    exit(0);
}

echo "Found $pendingCount pending PayChangu transactions. Starting verification...\n";

// Run the background job
$processed = paychangu_check_pending_transactions();

// Log the result
echo "PayChangu background job completed. Processed $processed out of $pendingCount pending transactions.\n";

// Rate limiting protection: sleep between batches if many transactions
if ($pendingCount > 10) {
    echo "Large number of pending transactions detected. Consider running cron less frequently.\n";
}
