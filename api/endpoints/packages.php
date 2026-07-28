<?php

/**
 * GET /api/packages
 * Get available internet packages
 */

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendJsonResponse(false, null, 'Method not allowed', 405);
}

$userId = requireAuth();

// Get customer information to determine account type
$customer = ORM::for_table('tbl_customers')->find_one($userId);
if (!$customer) {
    sendJsonResponse(false, null, 'Customer not found', 404);
}

$accountType = $customer['account_type'] ?? 'Personal';

// Get enabled plans for customer's account type
$plans = ORM::for_table('tbl_plans')
    ->where('enabled', '1')
    ->where('prepaid', 'yes')
    ->where('plan_type', $accountType)
    ->find_many();

$packagesData = [];
foreach ($plans as $plan) {
    // Get bandwidth information
    $bandwidth = ORM::for_table('tbl_bandwidth')->find_one($plan['id_bw']);
    
    $packagesData[] = [
        'id' => (int)$plan['id'],
        'name' => $plan['name_plan'],
        'type' => $plan['type'], // Hotspot, PPPOE, Balance
        'price' => (float)$plan['price'],
        'price_old' => (float)$plan['price_old'] ?: null,
        'validity' => (int)$plan['validity'],
        'validity_unit' => $plan['validity_unit'], // Mins, Hrs, Days, Months, Period
        'limit_type' => $plan['limit_type'], // Time_Limit, Data_Limit, Both_Limit, Unlimited
        'time_limit' => $plan['time_limit'] ?: null,
        'time_unit' => $plan['time_unit'] ?: null, // Mins, Hrs
        'data_limit' => $plan['data_limit'] ?: null,
        'data_unit' => $plan['data_unit'] ?: null, // MB, GB
        'shared_users' => $plan['shared_users'] ?: null,
        'bandwidth' => $bandwidth ? [
            'name' => $bandwidth['name_bw'],
            'rate_down' => $bandwidth['rate_down'],
            'rate_down_unit' => $bandwidth['rate_down_unit'],
            'rate_up' => $bandwidth['rate_up'],
            'rate_up_unit' => $bandwidth['rate_up_unit']
        ] : null,
        'is_radius' => (bool)$plan['is_radius'],
        'account_type' => $plan['plan_type']
    ];
}

sendJsonResponse(true, $packagesData);
