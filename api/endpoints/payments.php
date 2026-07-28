<?php

/**
 * GET /api/payments
 * Get customer payment history
 */

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendJsonResponse(false, null, 'Method not allowed', 405);
}

$userId = requireAuth();

// Get pagination parameters
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = isset($_GET['limit']) ? min(50, max(1, (int)$_GET['limit'])) : 20;
$offset = ($page - 1) * $limit;

// Get payment transactions for this customer
$query = ORM::for_table('tbl_payment_gateway')
    ->where('user_id', $userId)
    ->order_by_desc('created_date');

$total = $query->count();

$payments = $query
    ->offset($offset)
    ->limit($limit)
    ->find_many();

$paymentsData = [];
foreach ($payments as $payment) {
    $statusMap = [
        1 => 'unpaid',
        2 => 'paid',
        3 => 'failed',
        4 => 'canceled'
    ];

    $paymentsData[] = [
        'id' => (int)$payment['id'],
        'invoice' => $payment['trx_invoice'],
        'plan_name' => $payment['plan_name'],
        'plan_id' => (int)$payment['plan_id'],
        'price' => (float)$payment['price'],
        'gateway' => $payment['gateway'],
        'payment_method' => $payment['payment_method'],
        'payment_channel' => $payment['payment_channel'],
        'created_date' => $payment['created_date'],
        'paid_date' => $payment['paid_date'],
        'expired_date' => $payment['expired_date'],
        'status' => $statusMap[$payment['status']] ?? 'unknown',
        'router' => $payment['routers']
    ];
}

sendJsonResponse(true, [
    'payments' => $paymentsData,
    'pagination' => [
        'page' => $page,
        'limit' => $limit,
        'total' => $total,
        'total_pages' => ceil($total / $limit)
    ]
]);
