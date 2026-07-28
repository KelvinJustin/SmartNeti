<?php

/**
 * GET /api/profile
 * Get customer profile information
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

// Get active subscriptions
$subscriptions = ORM::for_table('tbl_user_recharges')
    ->where('customer_id', $userId)
    ->where('status', 'on')
    ->order_by_desc('expiration')
    ->find_many();

$activeSubscriptions = [];
foreach ($subscriptions as $sub) {
    $plan = ORM::for_table('tbl_plans')->find_one($sub['plan_id']);
    if ($plan) {
        $activeSubscriptions[] = [
            'id' => (int)$sub['id'],
            'plan_name' => $plan['name_plan'],
            'plan_type' => $plan['type'],
            'price' => $plan['price'],
            'recharged_on' => $sub['recharged_on'],
            'expiration' => $sub['expiration'],
            'status' => $sub['status'],
            'router' => $sub['routers']
        ];
    }
}

// Build profile data
$profileData = [
    'id' => (int)$customer['id'],
    'username' => $customer['username'],
    'fullname' => $customer['fullname'],
    'email' => $customer['email'],
    'phone' => $customer['phonenumber'],
    'address' => $customer['address'],
    'city' => $customer['city'],
    'status' => $customer['status'],
    'account_type' => $customer['account_type'],
    'balance' => (float)$customer['balance'],
    'service_type' => $customer['service_type'],
    'auto_renewal' => (bool)$customer['auto_renewal'],
    'created_at' => $customer['created_at'],
    'last_login' => $customer['last_login'],
    'active_subscriptions' => $activeSubscriptions
];

sendJsonResponse(true, $profileData);
