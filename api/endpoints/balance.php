<?php

/**
 * GET /api/balance
 * Get customer balance information
 */

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendJsonResponse(false, null, 'Method not allowed', 405);
}

$userId = requireAuth();

// Get customer information
$customer = ORM::for_table('tbl_customers')->find_one($userId);

if (!$customer) {
    sendJsonResponse(false, null, 'Customer not found', 404);
}

// Get recent balance transactions
$balanceTransactions = ORM::for_table('tbl_payment_gateway')
    ->where('user_id', $userId)
    ->where('routers', 'balance')
    ->order_by_desc('created_date')
    ->limit(10)
    ->find_many();

$transactions = [];
foreach ($balanceTransactions as $trx) {
    $transactions[] = [
        'id' => (int)$trx['id'],
        'plan_name' => $trx['plan_name'],
        'amount' => (float)$trx['price'],
        'payment_method' => $trx['payment_method'],
        'payment_channel' => $trx['payment_channel'],
        'created_date' => $trx['created_date'],
        'status' => $trx['status'] // 1=unpaid, 2=paid, 3=failed, 4=canceled
    ];
}

$balanceData = [
    'balance' => (float)$customer['balance'],
    'currency' => 'MWK', // Default currency, can be made configurable
    'status' => $customer['status'],
    'auto_renewal' => (bool)$customer['auto_renewal'],
    'recent_transactions' => $transactions
];

sendJsonResponse(true, $balanceData);
