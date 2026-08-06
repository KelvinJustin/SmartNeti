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

// Get router parameter from query string
$routerId = isset($_GET['router_id']) ? $_GET['router_id'] : null;

// Get plans based on router logic (matching web app)
if ($routerId == 'radius') {
    // Only radius plans
    $plans_pppoe = ORM::for_table('tbl_plans')
        ->where('plan_type', $accountType)
        ->where('enabled', '1')
        ->where('is_radius', 1)
        ->where('type', 'PPPOE')
        ->where('prepaid', 'yes')
        ->find_array();
    $plans_hotspot = ORM::for_table('tbl_plans')
        ->where('plan_type', $accountType)
        ->where('enabled', '1')
        ->where('is_radius', 1)
        ->where('type', 'Hotspot')
        ->where('prepaid', 'yes')
        ->find_array();
    $plans = array_merge($plans_pppoe, $plans_hotspot);
} elseif ($routerId === null) {
    // No router specified - show all plans (radius + non-radius)
    $radius_pppoe = ORM::for_table('tbl_plans')
        ->where('plan_type', $accountType)
        ->where('enabled', '1')
        ->where('is_radius', 1)
        ->where('type', 'PPPOE')
        ->where('prepaid', 'yes')
        ->find_array();
    $radius_hotspot = ORM::for_table('tbl_plans')
        ->where('plan_type', $accountType)
        ->where('enabled', '1')
        ->where('is_radius', 1)
        ->where('type', 'Hotspot')
        ->where('prepaid', 'yes')
        ->find_array();
    $plans_pppoe = ORM::for_table('tbl_plans')
        ->where('plan_type', $accountType)
        ->where('enabled', '1')
        ->where('is_radius', 0)
        ->where('type', 'PPPOE')
        ->where('prepaid', 'yes')
        ->find_array();
    $plans_hotspot = ORM::for_table('tbl_plans')
        ->where('plan_type', $accountType)
        ->where('enabled', '1')
        ->where('is_radius', 0)
        ->where('type', 'Hotspot')
        ->where('prepaid', 'yes')
        ->find_array();
    $plans_vpn = ORM::for_table('tbl_plans')
        ->where('plan_type', $accountType)
        ->where('enabled', '1')
        ->where('is_radius', 0)
        ->where('type', 'VPN')
        ->where('prepaid', 'yes')
        ->find_array();
    $plans = array_merge($radius_pppoe, $radius_hotspot, $plans_pppoe, $plans_hotspot, $plans_vpn);
} else {
    // Specific router plans
    $routers = ORM::for_table('tbl_routers')->where('id', $routerId)->find_many();
    $rs = [];
    foreach ($routers as $r) {
        $rs[] = $r['name'];
    }
    if (!empty($rs)) {
        $plans_pppoe = ORM::for_table('tbl_plans')
            ->where('plan_type', $accountType)
            ->where('enabled', '1')
            ->where_in('routers', $rs)
            ->where('is_radius', 0)
            ->where('type', 'PPPOE')
            ->where('prepaid', 'yes')
            ->find_array();
        $plans_hotspot = ORM::for_table('tbl_plans')
            ->where('plan_type', $accountType)
            ->where('enabled', '1')
            ->where_in('routers', $rs)
            ->where('is_radius', 0)
            ->where('type', 'Hotspot')
            ->where('prepaid', 'yes')
            ->find_array();
        $plans = array_merge($plans_pppoe, $plans_hotspot);
    } else {
        $plans = [];
    }
}

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
