<?php

/**
 * POST /api/balance/transfer
 * Transfer balance to another user
 */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(false, null, 'Method not allowed', 405);
}

$userId = requireAuth();

// Get request data
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    sendJsonResponse(false, null, 'Invalid JSON input', 400);
}

$targetUsername = isset($input['target_username']) ? trim($input['target_username']) : '';
$amount = isset($input['amount']) ? (float)$input['amount'] : 0;

// Validate input
if (empty($targetUsername)) {
    sendJsonResponse(false, null, 'Target username is required', 400);
}

if ($amount <= 0) {
    sendJsonResponse(false, null, 'Amount must be greater than 0', 400);
}

// Get sender customer info
$sender = ORM::for_table('tbl_customers')->find_one($userId);

if (!$sender) {
    sendJsonResponse(false, null, 'Sender not found', 404);
}

// Check if sender has sufficient balance
if ($sender['balance'] < $amount) {
    sendJsonResponse(false, null, 'Insufficient balance', 400);
}

// Check if target user exists
$target = ORM::for_table('tbl_customers')
    ->where('username', $targetUsername)
    ->find_one();

if (!$target) {
    sendJsonResponse(false, null, 'Target user not found', 404);
}

// Prevent self-transfer
if ($target['id'] == $userId) {
    sendJsonResponse(false, null, 'Cannot transfer to yourself', 400);
}

// Perform the transfer using Balance class
require_once __DIR__ . '/../../system/autoload/Balance.php';

$transferResult = Balance::transfer($userId, $targetUsername, $amount);

if ($transferResult) {
    // Log the transfer
    _log("Balance transfer: User " . $sender['username'] . " transferred " . $amount . " to " . $targetUsername);
    
    sendJsonResponse(true, [
        'message' => 'Balance transferred successfully',
        'amount' => $amount,
        'target_username' => $targetUsername,
        'sender_balance' => (float)$sender['balance'] - $amount,
        'target_balance' => (float)$target['balance'] + $amount
    ]);
} else {
    sendJsonResponse(false, null, 'Transfer failed', 500);
}
